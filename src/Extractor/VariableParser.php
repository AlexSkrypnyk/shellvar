<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Extractor;

use AlexSkrypnyk\Shellvar\Utils;

/**
 * Class VariableParser.
 *
 * Parses variable strings.
 */
class VariableParser {

  /**
   * Parse variable description from multiple lines.
   *
   * @param array<string> $lines
   *   A list of lines to extract a variable description from.
   * @param int $line_num
   *   A line number to start from.
   * @param array<string> $skip_prefixes
   *   A list of prefixes to skip.
   * @param string $comment_separator
   *   A comment delimiter.
   *
   * @return string
   *   A variable description.
   */
  public static function parseDescription(array $lines, $line_num, array $skip_prefixes = [], $comment_separator = '#'): string {
    $comment_lines = [];

    $line_num = min($line_num, count($lines) - 1);

    // Look behind until the first non-comment line.
    while ($line_num > 0 && str_starts_with(trim($lines[$line_num - 1]), $comment_separator)) {
      $comment_lines[] = trim(ltrim(trim($lines[$line_num - 1]), $comment_separator));
      $line_num--;
    }

    $comment_lines = array_reverse($comment_lines);

    $comment_lines = array_filter($comment_lines, static function (string $value) use ($skip_prefixes, $comment_separator): bool {
      foreach ($skip_prefixes as $prefix) {
        if (str_starts_with($value, ltrim($prefix, $comment_separator))) {
          return FALSE;
        }
      }

      return TRUE;
    });

    return implode("\n", $comment_lines);
  }

  /**
   * Parse variable value from a line.
   *
   * It is already known that the line contains a variable assignment.
   *
   * @param string $line
   *   A line to extract a variable value from.
   * @param string $unset_value
   *   The value to return if a value was not set or not extracted.
   *
   * @return string
   *   A parsed variable value or unset value if unable to parse or variable is
   *   not set.
   */
  public static function parseValue($line, string $unset_value): string {
    [$name, $value] = explode('=', $line, 2);

    $value = trim($value);

    if (empty($value)) {
      return $unset_value;
    }

    self::validateValue($value);

    $value = self::resolveNestedNotations($value, $unset_value);
    $value = Utils::removeDoubleQuotes($value);

    if ($name === self::unwrapNotation($value)) {
      $value = NULL;
    }

    return is_null($value) ? $unset_value : $value;
  }

  /**
   * Validate a value.
   *
   * @param string $value
   *   A value to validate.
   */
  protected static function validateValue(string $value): void {
    // Replace double quotes enclosed by single quotes with a placeholder to
    // not count them.
    $processed = preg_replace("/'[^']*\"[^']*'/", '', $value) ?? $value;

    // Remove escaped double quotes to only count unescaped ones.
    $processed = preg_replace('/\\\\\"/', '', (string) $processed) ?? $processed;

    // Even number of quotes in the processed value.
    if (str_contains($processed, '"') && substr_count($processed, '"') % 2 !== 0) {
      throw new \RuntimeException('Invalid number of quotes in the value: ' . $value);
    }

    // Even number of braces.
    if ((str_contains($value, '{') || str_contains($value, '}')) && substr_count($value, '}') !== substr_count($value, '{')) {
      throw new \RuntimeException('Unbalanced braces in the value: ' . $value);
    }
  }

  /**
   * Simplify the value notation by resolving nested notations.
   *
   * Do not optimise this method. It has explicit conditional branches
   * to easily understand the logic and debug it.
   *
   * @param string $value
   *   A value to resolve nested notations in.
   * @param string $unset_value
   *   A value to use for when the value is not set.
   *
   * @return string
   *   A value with resolved nested notations.
   *
   * @throws \Exception
   */
  protected static function resolveNestedNotations(string $value, string $unset_value): string {
    $value = self::normaliseNotation($value);

    $updated_value = $value;

    $tokens = [];

    while (!empty($updated_value)) {
      $replaced_count = 0;

      // Match for ${...} variable notation and replace it with a token while
      // also collecting tokens for further replacement.
      $updated_value = preg_replace_callback('/\$\{[^{}]+}/', static function (array $matches) use ($unset_value, &$replaced_count, &$tokens): string {
        $notation = $matches[0];

        if (self::notationIsVariable($notation)) {
          $token_name = 'SHELVAR_TEMP_TOKEN_' . count($tokens);
          $tokens[$token_name] = self::resolveNotation($notation, $unset_value);
          $notation = $token_name;
          $replaced_count++;
        }

        return $notation;
      }, $updated_value);

      if ($updated_value !== NULL) {
        $value = $updated_value;
      }

      if ($replaced_count === 0) {
        break;
      }
    }

    if (!empty($tokens)) {
      $value = Utils::recursiveStrtr($value, $tokens);
    }

    return $value;
  }

  /**
   * Given ${VAR1:-${VAR2-val2}} notation, resolve the variable value.
   *
   * @param string $notation
   *   Variable notation.
   * @param string $unset_value
   *   A value to use for when the value is not set.
   *
   * @return string
   *   A resolved value.
   */
  protected static function resolveNotation(string $notation, string $unset_value): string {
    $notation = self::unwrapNotation($notation);

    $parsed = VariableParser::parseNotation($notation);

    $value = '$' . $parsed['name'];
    $value = self::normaliseNotation($value);

    // Return the default value from the nested variable notation or use the
    // default value.
    if (!is_null($parsed['default'])) {
      $value = $parsed['default'];
      if (self::notationIsVariable($parsed['default'])) {
        $value = self::resolveNestedNotations($parsed['default'], $unset_value);
      }
    }
    elseif (is_numeric($parsed['name'])) {
      $value = $unset_value;
    }

    return $value;
  }

  /**
   * Parse a value notation.
   *
   * @param string $string
   *   A value notation to parse.
   *
   * @return array<string, string|null>
   *   An array representation of a parsed value notation with the following:
   *   - name: The variable name.
   *   - operator: The operator.
   *   - default: The default value.
   */
  protected static function parseNotation(string $string): array {
    $parts = [
      'name' => $string,
      'operator' => NULL,
      'default' => NULL,
    ];

    preg_match('/([^:+=?-]+)(-|:-|:=|\+=|:\?|\?)(.+)?/', $string, $matches);

    if (empty($matches)) {
      return $parts;
    }

    $parts = [
      'name' => array_slice($matches, 1)[0] ?? $string,
      'operator' => array_slice($matches, 1)[1] ?? NULL,
      'default' => array_slice($matches, 1)[2] ?? NULL,
    ];

    if ($parts['operator'] === '?' || $parts['operator'] === ':?') {
      $parts['default'] = NULL;
    }

    return $parts;
  }

  /**
   * Check if a notation is a variable.
   *
   * @param string $value
   *   A value to check.
   *
   * @return bool
   *   TRUE if the value is a variable, FALSE otherwise.
   */
  protected static function notationIsVariable(string $value): bool {
    $value = Utils::removeDoubleQuotes($value);

    return str_starts_with($value, '$');
  }

  /**
   * Normalise variable notation.
   *
   * @param string $notation
   *   A variable notation.
   *
   * @return string
   *   Normalised variable notation.
   */
  protected static function normaliseNotation(string $notation): string {
    if (!str_starts_with($notation, '"') && !str_ends_with($notation, '"')) {
      $notation = '"' . $notation . '"';
    }

    if (str_starts_with($notation, '"$') && !str_starts_with($notation, '"${') && !str_starts_with($notation, '"$(')) {
      $notation = '"${' . substr($notation, 2, -1) . '}"';
    }

    return $notation;
  }

  /**
   * Unwrap variable notation.
   *
   * @param string $notation
   *   A variable notation.
   *
   * @return string
   *   Unwrapped variable notation.
   */
  protected static function unwrapNotation($notation): string {
    if (str_starts_with($notation, '"')) {
      $notation = substr($notation, 1);
    }

    if (str_ends_with($notation, '"')) {
      $notation = substr($notation, 0, -1);
    }

    if (str_starts_with($notation, '${')) {
      $notation = substr($notation, 2);
    }

    if (str_ends_with($notation, '}')) {
      $notation = substr($notation, 0, -1);
    }

    return $notation;
  }

}

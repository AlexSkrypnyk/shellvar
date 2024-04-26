<?php

namespace AlexSkrypnyk\Shellvar\Extractor;

/**
 * Class VariableParser.
 *
 * Parses variable strings.
 */
class VariableParser {

  /**
   * Extract variable value from a line.
   *
   * It is already known that the line contains a variable assignment.
   *
   * @param string $line
   *   A line to extract a variable value from.
   * @param mixed $default_value
   *   The default value to return if a value was not extracted.
   *
   * @return string|mixed
   *   A variable value.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public static function parseValue($line, mixed $default_value) {
    [, $value] = explode('=', $line, 2);

    $value = trim($value);

    if (empty($value) || !is_string($value)) {
      return $default_value;
    }

    self::validateValue($value);

    $value = self::resolveNestedNotations($value);

    $value = trim($value, '"');

    if (str_starts_with($value, '$')) {
      if (str_starts_with($value, '${')) {
        $value = trim($value, '${}');
        $value = trim($value, '"');

        $parsed = VariableParser::parseValueNotation($value);

        $value = trim($parsed['default'] ?? '$' . $parsed['name'], '"');
      }

      // Numeric values are script arguments, so we convert them to
      // the default value.
      $value = str_starts_with($value, '$') && is_numeric(trim($value, '$')) ? $default_value : $value;

      $value = is_string($value) ? trim($value, '$') : $value;
    }

    return empty($value) ? $default_value : $value;
  }

  /**
   * Simplify the value notation by resolving nested notations.
   *
   * @param string $value
   *   A value to resolve nested notations in.
   *
   * @return string
   *   A value with resolved nested notations.
   */
  protected static function resolveNestedNotations(string $value): string {
    $updated_value = $value;

    while (!empty($updated_value)) {
      $replaced_count = 0;

      // Match `<name><operator><value>` notation.
      $regex = '/\$\{(?:[^{}]*|\{[^{}]*})*\$\{([^{}]+)}(?:[^{}]*|\{[^{}]*})*}/';
      $updated_value = preg_replace_callback($regex, static function (array $matches) use (&$replaced_count): string {
        $original = $matches[0];
        $notation = $matches[1];

        $parsed = VariableParser::parseValueNotation($notation);

        // Use the found default value from the notation or the
        // variable name itself if the notation does not have a default value.
        $replace_value = trim($parsed['default'] ?? '$' . $parsed['name'], '"');

        // Wrap the notation in `${}` and replace it with a resolved replacement
        // value.
        $replaced = str_replace('${' . $notation . '}', $replace_value, $original);

        $replaced_count++;

        return $replaced;
      }, $updated_value);

      if ($replaced_count === 0) {
        if ($updated_value !== NULL) {
          $value = $updated_value;
        }
        break;
      }
    }

    return (string) $value;
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
  protected static function parseValueNotation(string $string): array {
    $parts = [
      'name' => $string,
      'operator' => NULL,
      'default' => NULL,
    ];

    preg_match('/([^:+=?-]+)(-|:-|:=|\+=|\?)(.+)?/', $string, $matches);

    if (empty($matches)) {
      return $parts;
    }

    $parts = [
      'name' => array_slice($matches, 1)[0] ?? $string,
      'operator' => array_slice($matches, 1)[1] ?? NULL,
      'default' => array_slice($matches, 1)[2] ?? NULL,
    ];

    if ($parts['operator'] === '?') {
      $parts['default'] = NULL;
    }

    return $parts;
  }

  /**
   * Extract variable description from multiple lines.
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

    $comment_lines = array_filter($comment_lines, static function ($value) use ($skip_prefixes, $comment_separator): bool {
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
   * Validate a value.
   *
   * @param string $value
   *   A value to validate.
   */
  public static function validateValue(string $value): void {
    // Even number of quotes.
    if (str_contains($value, '"') && substr_count($value, '"') % 2 !== 0) {
      throw new \RuntimeException('Invalid number of quotes in the value: ' . $value);
    }

    // Even number of braces.
    if ((str_contains($value, '{') || str_contains($value, '}')) && substr_count($value, '}') !== substr_count($value, '{')) {
      throw new \RuntimeException('Unbalanced braces in the value: ' . $value);
    }
  }

}

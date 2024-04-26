<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Extractor;

use AlexSkrypnyk\Shellvar\Utils;
use AlexSkrypnyk\Shellvar\Variable\Variable;

/**
 * Class ShellExtractor.
 *
 * Extracts variables from shell scripts.
 */
class ShellExtractor extends AbstractExtractor {

  /**
   * Defines a comment separator.
   */
  final const COMMENT_SEPARATOR = '#';

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'extractor-shell';
  }

  /**
   * {@inheritdoc}
   */
  public function extract(): array {
    parent::extract();

    // Exclude non-assignments.
    array_walk($this->variables, static function (&$var): void {
      $var = $var->getIsAssignment() ? $var : FALSE;
    });
    $this->variables = array_filter($this->variables);

    return $this->variables;
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  protected function extractVariablesFromFile(string $filepath): void {
    $skip = is_scalar($this->config->get('skip-text')) ? (string) $this->config->get('skip-text') : '';

    $lines = Utils::getLinesFromFiles([$filepath]);

    foreach ($lines as $num => $line) {
      $var = $this->extractVariable($line);

      if (!$var instanceof Variable) {
        continue;
      }

      $absolute_filepath = realpath($filepath);

      if ($absolute_filepath === FALSE) {
        throw new \RuntimeException('Failed to resolve the absolute path for the file: ' . $filepath);
      }

      $var->addPath($absolute_filepath);

      if ($var->getIsAssignment()) {
        $default_value = $this->extractVariableValue($line, $this->config->get('unset'));
        // Assign a value, but not if it defaults to a variable name.
        if ($default_value && $default_value !== $var->getName()) {
          $var->setDefaultValue($default_value);
        }
      }

      $description_prefix = is_array($this->config->get('skip-description-prefix')) ? $this->config->get('skip-description-prefix') : [];
      $description = $this->extractVariableDescription($lines, $num, $description_prefix);
      if (!empty($description)) {
        if ($skip && str_contains($description, $skip)) {
          continue;
        }

        $var->setDescription($description);
      }

      if (!empty($this->variables[$var->getName()])) {
        $this->variables[$var->getName()]->merge($var);
      }
      else {
        $this->variables[$var->getName()] = $var;
      }
    }
  }

  /**
   * Extract variable from a line.
   *
   * @param string $line
   *   A line to extract a variable name from.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable|null
   *   Variable instance or NULL if a variable was not extracted.
   */
  protected function extractVariable(string $line): ?Variable {
    $line = trim($line);

    if (str_starts_with(trim($line), self::COMMENT_SEPARATOR)) {
      return NULL;
    }

    // Assignment with inline code (assessing start is enough).
    if (preg_match('/^`([a-zA-Z]\w*)=.*$/', $line, $matches)) {
      return NULL;
    }

    // Assignment.
    if (preg_match('/^([a-zA-Z]\w*)=.*$/', $line, $matches)) {
      return (new Variable($matches[1]))->setIsAssignment(TRUE);
    }

    // Usage as `${variable}`.
    if (preg_match('/`\${([a-zA-Z]\w*)}`/', $line, $matches)) {
      return (new Variable($matches[1]))->setIsInlineCode(TRUE);
    }

    // Usage as ${variable}.
    if (preg_match('/\${([a-zA-Z]\w*)}/', $line, $matches)) {
      return new Variable($matches[1]);
    }

    // Usage as `$variable`.
    if (preg_match('/`\$([a-zA-Z]\w*)`/', $line, $matches)) {
      return (new Variable($matches[1]))->setIsInlineCode(TRUE);
    }

    // Usage as $variable.
    if (preg_match('/\$([a-zA-Z]\w*)/', $line, $matches)) {
      return new Variable($matches[1]);
    }

    return NULL;
  }

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
   */
  protected function extractVariableValue($line, mixed $default_value) {
    [, $value] = explode('=', $line, 2);

    $value = trim($value);

    if (empty($value)) {
      return $default_value;
    }

    // Validate value.
    // Even number of quotes.
    if (str_contains($value, '"') && substr_count($value, '"') % 2 !== 0) {
      throw new \RuntimeException('Invalid number of quotes in the value: ' . $value);
    }
    // Even number of braces.
    if ((str_contains($value, '{') || str_contains($value, '}')) && substr_count($value, '}') !== substr_count($value, '{')) {
      throw new \RuntimeException('Unbalanced braces in the value: ' . $value);
    }

    // Replace all outermost matching patterns with the found sub-group. This
    // allows to reduce the value to the innermost matching pattern.
    while (TRUE) {
      $replaced_count = 0;

      $regex = '/\$\{(?:[^{}]*|\{[^{}]*\})*\$\{([^{}]+)\}(?:[^{}]*|\{[^{}]*\})*\}/';
      $value = preg_replace_callback($regex, static function ($matches) use (&$replaced_count): string {
        $to_replace = $matches[0];

        $innermost = $matches[1] ?? NULL;
        if (!$innermost) {
          return '';
        }

        $parsed = self::parseVariableString($innermost);
        if (!$parsed) {
          return '';
        }

        $replace_with = trim($parsed['default'] ?? '$' . $parsed['name'], '"');
        $replaced = str_replace('${' . $innermost . '}', $replace_with, $to_replace);

        $replaced_count++;

        return $replaced;
      }, (string) $value);

      if ($replaced_count === 0) {
        break;
      }
    }

    $value = trim((string) $value, '"');

    if (str_starts_with($value, '$')) {
      if (str_starts_with($value, '${')) {
        $value = trim($value, '${}');
        $value = trim($value, '"');

        $parsed = self::parseVariableString($value);
        if (!$parsed) {
          return $default_value;
        }

        $value = trim($parsed['default'] ?? '$' . $parsed['name'], '"');
      }

      // Numeric values are script arguments, so we convert them to defaults.
      $value = str_starts_with($value, '$') && is_numeric(trim($value, '$')) ? $default_value : $value;

      $value = trim((string) $value, '$');
    }

    return empty($value) ? $default_value : $value;
  }

  /**
   * Parse a variable string.
   *
   * @param string $string
   *   A variable string to parse.
   *
   * @return array|null
   *   An array representation of a parsed variable string with the following:
   *   - name: The variable name.
   *   - operator: The operator.
   *   - default: The default value.
   */
  protected static function parseVariableString(string $string): ?array {
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
  protected function extractVariableDescription(array $lines, $line_num, array $skip_prefixes = [], $comment_separator = self::COMMENT_SEPARATOR): string {
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

}

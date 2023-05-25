<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Extractor;

use AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable;
use AlexSkrypnyk\ShellVariablesExtractor\Utils;

/**
 * Class ShellExtractor.
 *
 * Extracts variables from shell scripts.
 */
class ShellExtractor extends AbstractExtractor {

  /**
   * Defines a comment separator.
   */
  const COMMENT_SEPARATOR = '#';

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
    array_walk($this->variables, function (&$var) {
      $var = !$var->getIsAssignment() ? FALSE : $var;
    });
    $this->variables = array_filter($this->variables);

    return $this->variables;
  }

  /**
   * {@inheritdoc}
   */
  protected function extractVariablesFromFile($file): void {
    $lines = Utils::getLinesFromFiles([$file]);

    foreach ($lines as $num => $line) {
      $var = $this->extractVariable($line);

      if (!$var) {
        continue;
      }

      $var->addPath($file);

      if ($var->getIsAssignment()) {
        $default_value = $this->extractVariableValue($line, $this->config->get('unset'));
        // Assign a value, but not if it defaults to a variable name.
        if ($default_value && $default_value !== $var->getName()) {
          $var->setDefaultValue($default_value);
        }
      }

      $description = $this->extractVariableDescription($lines, $num, $this->config->get('skip-description-prefix'));
      if ($description) {
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
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable|null
   *   Variable instance or NULL if a variable was not extracted.
   */
  protected function extractVariable(string $line) {
    $line = trim($line);

    if (str_starts_with(trim($line), self::COMMENT_SEPARATOR)) {
      return NULL;
    }

    // Assignment with inline code (assessing start is enough).
    if (preg_match('/^`([a-zA-Z][a-zA-Z0-9_]*)=.*$/', $line, $matches)) {
      return NULL;
    }

    // Assignment.
    if (preg_match('/^([a-zA-Z][a-zA-Z0-9_]*)=.*$/', $line, $matches)) {
      return (new Variable($matches[1]))->setIsAssignment(TRUE);
    }

    // Usage as `${variable}`.
    if (preg_match('/`\${([a-zA-Z][a-zA-Z0-9_]*)}`/', $line, $matches)) {
      return (new Variable($matches[1]))->setIsInlineCode(TRUE);
    }

    // Usage as ${variable}.
    if (preg_match('/\${([a-zA-Z][a-zA-Z0-9_]*)}/', $line, $matches)) {
      return new Variable($matches[1]);
    }

    // Usage as `$variable`.
    if (preg_match('/`\$([a-zA-Z][a-zA-Z0-9_]*)`/', $line, $matches)) {
      return (new Variable($matches[1]))->setIsInlineCode(TRUE);
    }

    // Usage as $variable.
    if (preg_match('/\$([a-zA-Z][a-zA-Z0-9_]*)/', $line, $matches)) {
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
   * @param string $default_value
   *   The default value to return if a value was not extracted.
   *
   * @return string
   *   A variable value.
   */
  protected function extractVariableValue($line, string $default_value) {
    [, $value] = explode('=', $line, 2);

    $value = trim($value);

    if (empty($value)) {
      return $default_value;
    }

    // Replace all outermost matching patterns with the found sub-group. This
    // allows to reduce the value to the innermost matching pattern.
    while (TRUE) {
      $replaced = 0;
      $value = preg_replace_callback('/\$\{([^:\-}]+):-([^}]+)?}/', function ($matches) use (&$replaced) {
        $replaced++;

        // Use found value or the default value, i.e. in case of ${var:-abc},
        // use 'abc'; in case of ${var:-}, use 'var', but as a variable to make
        // sure that it is not confused with a scalar value ($var vs 'var').
        return trim($matches[2] ?? '$' . $matches[1], '"');
      }, $value);

      if ($replaced === 0) {
        break;
      }
    }

    $value = trim($value, '"');

    if (str_starts_with($value, '$')) {
      if (str_starts_with($value, '${')) {
        $value = trim($value, '${}');
        $value = trim($value, '"');
      }

      $value = trim($value, '$');

      // Numeric values are script arguments, so we convert them to defaults.
      $value = is_numeric($value) ? $default_value : $value;
    }

    return empty($value) ? $default_value : $value;
  }

  /**
   * Extract variable description from multiple lines.
   *
   * @param array $lines
   *   A list of lines to extract a variable description from.
   * @param int $line_num
   *   A line number to start from.
   * @param array $skip_prefixes
   *   A list of prefixes to skip.
   * @param string $comment_separator
   *   A comment delimiter.
   *
   * @return string
   *   A variable description.
   */
  protected function extractVariableDescription($lines, $line_num, array $skip_prefixes = [], $comment_separator = self::COMMENT_SEPARATOR) {
    $comment_lines = [];

    $line_num = min($line_num, count($lines) - 1);

    // Look behind until the first non-comment line.
    while ($line_num > 0 && str_starts_with(trim($lines[$line_num - 1]), $comment_separator)) {
      $comment_lines[] = trim(ltrim(trim($lines[$line_num - 1]), $comment_separator));
      $line_num--;
    }

    $comment_lines = array_reverse($comment_lines);

    $comment_lines = array_filter($comment_lines, function ($value) use ($skip_prefixes, $comment_separator) {
      foreach ($skip_prefixes as $prefix) {
        if (str_starts_with($value, ltrim($prefix, $comment_separator))) {
          return FALSE;
        }
      }

      return TRUE;
    });

    array_walk($comment_lines, function (&$value) {
      $value = empty($value) ? "\n" : trim($value);
    });

    $output = implode(' ', $comment_lines);
    $output = str_replace([" \n ", " \n", "\n "], "\n", $output);
    $output = trim($output, "\n");

    return $output;
  }

}

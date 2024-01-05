<?php

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
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function extractVariablesFromFile($file): void {
    $skip = $this->config->get('skip-text');
    // @phpstan-ignore-next-line
    $lines = Utils::getLinesFromFiles([$file]);

    foreach ($lines as $num => $line) {
      $var = $this->extractVariable($line);

      if (!$var) {
        continue;
      }

      // @phpstan-ignore-next-line
      $var->addPath(realpath($file));

      if ($var->getIsAssignment()) {
        $default_value = $this->extractVariableValue($line, $this->config->get('unset'));
        // Assign a value, but not if it defaults to a variable name.
        if ($default_value && $default_value !== $var->getName()) {
          $var->setDefaultValue($default_value);
        }
      }

      $description = $this->extractVariableDescription($lines, $num, $this->config->get('skip-description-prefix'));
      if ($description) {
        // @phpstan-ignore-next-line
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
        // @phpstan-ignore-next-line
      }, $value);

      if ($replaced === 0) {
        break;
      }
    }
    // @phpstan-ignore-next-line
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
   * @param array<string> $lines
   *   A list of lines to extract a variable description from.
   * @param int $line_num
   *   A line number to start from.
   * @param mixed $skip_prefixes
   *   A list of prefixes to skip.
   * @param string $comment_separator
   *   A comment delimiter.
   *
   * @return string
   *   A variable description.
   */
  protected function extractVariableDescription(array $lines, $line_num, mixed $skip_prefixes = [], $comment_separator = self::COMMENT_SEPARATOR) {
    $comment_lines = [];

    $line_num = min($line_num, count($lines) - 1);

    // Look behind until the first non-comment line.
    while ($line_num > 0 && str_starts_with(trim($lines[$line_num - 1]), $comment_separator)) {
      $comment_lines[] = trim(ltrim(trim($lines[$line_num - 1]), $comment_separator));
      $line_num--;
    }

    $comment_lines = array_reverse($comment_lines);

    $comment_lines = array_filter($comment_lines, function ($value) use ($skip_prefixes, $comment_separator) {
      // @phpstan-ignore-next-line
      foreach ($skip_prefixes as $prefix) {
        // @phpstan-ignore-next-line
        if (str_starts_with($value, ltrim($prefix, $comment_separator))) {
          return FALSE;
        }
      }

      return TRUE;
    });

    return implode("\n", $comment_lines);
  }

}

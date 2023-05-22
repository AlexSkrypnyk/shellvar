<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Extractor;

use AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable;

/**
 * Class ShellVariablesExtractorCommand.
 *
 * Extracts variables from shell scripts.
 */
class Extractor implements ExtractorInterface {

  const COMMENT_SEPARATOR = '#';

  /**
   * Array of target files.
   *
   * @var array
   */
  protected $targets;

  /**
   * Array of configuration options passed from the CLI.
   *
   * @var array
   */
  protected $config;

  /**
   * Command constructor.
   *
   * @param array $targets
   *   Array of target files.
   * @param array $config
   *   Array of configuration options.
   */
  public function __construct(array $targets, array $config) {
    $this->targets = $targets;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function extract(array $targets): array {
    $vars = [];

    // Extract all variables.
    foreach ($targets as $target) {
      $vars += $this->extractVariablesFromFile($target);
    }

    // Filter-out excluded local variables.
    if ($this->config['globals-only']) {
      $vars = $this->filterLocalVars($vars);
    }

    // Filter-out excluded variables.
    $vars = $this->filterExcludedVars($vars, $this->getLinesFromFiles($this->config['exclude-file']));

    // Filter-out excluded prefixed variables.
    $vars = $this->filterExcludedPrefixedVars($vars, $this->config['exclude-prefix']);

    // Sort variables by name.
    if ($this->config['sort']) {
      ksort($vars);
    }

    // Exclude non-assignments.
    array_walk($vars, function (&$var) {
      $var = !$var->getIsAssignment() ? FALSE : $var;
    });
    $vars = array_filter($vars);

    return $vars;
  }

  /**
   * Extract variables from file.
   *
   * @param string $file
   *   Path to file.
   */
  protected function extractVariablesFromFile($file) {
    $vars = [];

    $lines = $this->getLinesFromFiles([$file]);

    foreach ($lines as $num => $line) {
      $var = $this->extractVariable($line);

      if (!$var) {
        continue;
      }

      // Only use the very first occurrence.
      if (!empty($vars[$var->getName()])) {
        continue;
      }

      if ($var->getIsAssignment()) {
        $default_value = $this->extractVariableValue($line, $this->config['unset']);
        if ($default_value) {
          $var->setDefaultValue($default_value);
        }
      }

      $description = $this->extractVariableDescription($lines, $num);
      if ($description) {
        $var->setDescription($description);
      }

      $vars[$var->getName()] = $var;
    }

    return $vars;
  }

  /**
   * Get lines from files.
   *
   * @param array $paths
   *   A list of paths to files.
   * @param string $eol
   *   An end of line character.
   *
   * @return array
   *   A list of lines, merged into one array.
   */
  protected function getLinesFromFiles($paths, $eol = "\n") {
    $lines = [];

    foreach ($paths as $path) {
      $lines = array_merge($lines, explode($eol, file_get_contents($path)));
    }

    return $lines;
  }

  /**
   * Extract variable from a line.
   *
   * @param string $line
   *   A line to extract a variable name from.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable|null
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
        // use 'abc'; in case of ${var:-}, use 'var'.
        return trim($matches[2] ?? $matches[1], '"');
      }, $value);

      if ($replaced === 0) {
        break;
      }
    }

    $value = trim($value, '"');

    if (str_starts_with($value, '${')) {
      $value = trim($value, '${}');
      $value = trim($value, '"');
    }
    if (str_starts_with($value, '$')) {
      $value = trim($value, '$');
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
   * @param string $comment_separator
   *   A comment delimiter.
   *
   * @return string
   *   A variable description.
   */
  protected function extractVariableDescription($lines, $line_num, $comment_separator = self::COMMENT_SEPARATOR) {
    $comment_lines = [];

    $line_num = min($line_num, count($lines) - 1);

    // Look behind until the first non-comment line.
    while ($line_num > 0 && str_starts_with(trim($lines[$line_num - 1]), $comment_separator)) {
      $comment_lines[] = trim(ltrim(trim($lines[$line_num - 1]), $comment_separator));
      $line_num--;
    }

    $comment_lines = array_reverse($comment_lines);
    array_walk($comment_lines, function (&$value) {
      $value = empty($value) ? "\n" : trim($value);
    });

    $output = implode(' ', $comment_lines);
    $output = str_replace([" \n ", " \n", "\n "], "\n", $output);

    return $output;
  }

  /**
   * Filter out local variables.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[] $vars
   *   A list of variables to filter.
   */
  protected function filterLocalVars(array $vars): array {
    return array_filter($vars, function (Variable $variable) {
      return $variable->getName() != strtolower($variable->getName());
    });
  }

  /**
   * Filter out excluded variables.
   */
  protected function filterExcludedVars(array $vars, array $excluded): array {
    return array_diff_key($vars, array_flip($excluded));
  }

  /**
   * Filter out excluded prefixed variables.
   */
  protected function filterExcludedPrefixedVars(array $vars, array $prefixes): array {
    return array_filter($vars, function (Variable $variable) use ($prefixes) {
      return !array_filter($prefixes, fn($p) => str_starts_with($variable->getName(), $p));
    });
  }

}

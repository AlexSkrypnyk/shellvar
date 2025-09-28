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
    array_walk($this->variables, static function (Variable &$var): void {
      $var = $var->getIsAssignment() ? $var : FALSE;
    });
    // @phpstan-ignore-next-line
    $this->variables = array_filter($this->variables);

    return $this->variables;
  }

  /**
   * {@inheritdoc}
   */
  protected function extractVariablesFromFile(string $filepath): void {
    $skip = is_scalar($this->config->get('skip-text')) ? (string) $this->config->get('skip-text') : '';

    $lines = Utils::getLinesFromFiles([$filepath]);

    $lines = self::concatenateLines($lines);

    foreach ($lines as $num => $line) {
      $var = $this->extractVariable($line);

      if (!$var instanceof Variable) {
        continue;
      }

      $absolute_filepath = realpath($filepath);

      if ($absolute_filepath === FALSE) {
        // @codeCoverageIgnoreStart
        throw new \RuntimeException('Failed to resolve the absolute path for the file: ' . $filepath);
        // @codeCoverageIgnoreEnd
      }

      $var->addPath($absolute_filepath);

      if ($var->getIsAssignment()) {
        $default_value = VariableParser::parseValue($line, is_string($this->config->get('unset')) ? $this->config->get('unset') : 'UNSET');
        // Assign a value, but not if it defaults to a variable name.
        if ($default_value !== $var->getName()) {
          $var->setDefaultValue($default_value);
        }
      }

      $description_prefix = $this->config->get('skip-description-prefix');
      $description_prefix = is_array($description_prefix) ? array_filter($description_prefix, 'is_string') : [];

      $description = VariableParser::parseDescription($lines, $num, $description_prefix, self::COMMENT_SEPARATOR);
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
   * Concatenate lines that look like multi-sline strings.
   *
   * @param array<int, string> $lines
   *   An array of lines to concatenate.
   *
   * @return array<int, string>
   *   An array of concatenated lines.
   */
  protected function concatenateLines(array $lines): array {
    $merged_lines = [];

    $carry = '';
    $is_concatenating = FALSE;

    foreach ($lines as $line) {
      // Replace double quotes enclosed by single quotes with a placeholder to
      // not count them.
      $processed = preg_replace("/'[^']*\"[^']*'/", '', $line) ?? $line;

      // Remove escaped double quotes to only count unescaped ones.
      $processed = preg_replace('/\\\\\"/', '', (string) $processed) ?? $processed;

      // Count unescaped double quotes.
      $quote_count = substr_count($processed, '"');

      // Toggle concatenating mode if the number of quotes is odd.
      if ($quote_count % 2 !== 0) {
        $is_concatenating = !$is_concatenating;
      }

      if ($is_concatenating) {
        $carry .= $line . PHP_EOL;
      }
      elseif (!empty($carry)) {
        $merged_lines[] = $carry . $line;
        $carry = '';
      }
      else {
        $merged_lines[] = $line;
      }
    }

    // @codeCoverageIgnoreStart
    if (!empty($carry)) {
      $merged_lines[] = trim($carry);
    }

    // @codeCoverageIgnoreEnd
    return $merged_lines;
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
    if (preg_match('/^([a-zA-Z]\w*)=.*$/s', $line, $matches)) {
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

}

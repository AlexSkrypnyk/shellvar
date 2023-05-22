<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

/**
 * Class AbstractMarkdownFormatter.
 *
 * Abstract formatter class to be extended by all Markdown formatters.
 */
abstract class AbstractMarkdownFormatter extends AbstractFormatter {

  /**
   * {@inheritdoc}
   */
  public function format(): string {
    $this->processVariables();

    return $this->doFormat();
  }

  /**
   * Render variables data as a Markdown string.
   *
   * @return string
   *   A rendered Markdown string.
   */
  abstract protected function doFormat(): string;

  /**
   * Process variables data.
   */
  protected function processVariables(): void {
    if ($this->config['md-inline-code-wrap-vars']) {
      $this->variables = $this->processInlineCode($this->variables);
    }

    if ($this->config['md-link-vars']) {
      $this->variables = $this->processLinks($this->variables);
    }
  }

  /**
   * Process inline code.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[] $variables
   *   A list of variables to process.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[]
   *   A list of processed variables.
   */
  protected function processInlineCode(array $variables): array {
    // Process all additional code items.
    $tokens = [];
    if (!empty($this->config['md-inline-code-extra-file'])) {
      $tokens = array_filter($this->getLinesFromFiles($this->config['md-inline-code-extra-file']));
    }

    foreach ($variables as $variable) {
      $variable->setName('`' . $variable->getName() . '`');
      if (!empty($variable->getDefaultValue())) {
        $variable->setDefaultValue('`' . $variable->getDefaultValue() . '`');
      }

      // Process all additional code items.
      foreach ($tokens as $token) {
        $token = trim($token);
        $a = preg_replace('/\b((?<!`)' . preg_quote($token, '/') . ')\b/', '`${1}`', $variable->getDescription());
        $variable->setDescription($a);
      }
    }

    return $variables;
  }

  /**
   * Link variables.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[] $variables
   *   A list of variables to process.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[]
   *   A list of processed variables.
   */
  protected function processLinks(array $variables): array {
    $variables_sorted = $variables;
    krsort($variables_sorted, SORT_NATURAL);

    foreach ($variables as $k => $variable) {
      // Replace in description.
      $replaced = [];
      foreach (array_keys($variables_sorted) as $other_name) {
        if (!str_contains($variable->getDescription(), $other_name)) {
          continue;
        }

        $already_added = (bool) count(array_filter($replaced, function ($v) use ($other_name) {
          return str_contains($v, $other_name);
        }));

        if ($already_added) {
          continue;
        }

        $other_name_slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $other_name));
        $replacement = sprintf('[$%s](#%s)', $other_name, $other_name_slug);

        $variable->setDescription(preg_replace('/`?\$?' . $other_name . '`?/', $replacement, $variable->getDescription()));
        $replaced[] = $other_name;
      }

      $variables[$k] = $variable;
    }

    return $variables;
  }

  /**
   * Get lines from files.
   *
   * @param array $paths
   *   A list of paths to files.
   *
   * @return array
   *   A list of lines, merged into one array.
   */
  protected function getLinesFromFiles($paths) {
    $lines = [];

    foreach ($paths as $path) {
      $lines = array_merge($lines, preg_split("/(\r\n|\n|\r)/", file_get_contents($path)));
    }

    return $lines;
  }

}

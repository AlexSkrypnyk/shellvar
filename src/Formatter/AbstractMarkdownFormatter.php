<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

use AlexSkrypnyk\ShellVariablesExtractor\Utils;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractMarkdownFormatter.
 *
 * Abstract formatter class to be extended by all Markdown formatters.
 */
abstract class AbstractMarkdownFormatter extends AbstractFormatter {

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions() {
    return array_merge(parent::getConsoleOptions(), [
      new InputOption(
        name: 'md-link-vars',
        mode: InputOption::VALUE_NONE,
        description: 'Link variables within usages to their definitions in the Markdown output format.'
      ),
      new InputOption(
        name: 'md-no-inline-code-wrap-vars',
        mode: InputOption::VALUE_NONE,
        description: 'Do not wrap variables to show them as inline code in the Markdown output format.'
      ),
      new InputOption(
        name: 'md-no-inline-code-wrap-numbers',
        mode: InputOption::VALUE_NONE,
        description: 'Do not wrap numbers to show them as inline code in the Markdown output format.'
      ),
      new InputOption(
        name: 'md-inline-code-extra-file',
        mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        description: 'A path to a file that contains additional strings to be formatted as inline code in the Markdown output format.',
        default: [],
      ),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function processConfig($config):void {
    parent::processConfig($config);
    $config->set('md-inline-code-extra-file', Utils::getNonEmptyLinesFromFiles(Utils::resolvePaths($config->get('md-inline-code-extra-file'))));
  }

  /**
   * Process variables data.
   */
  protected function processVariables(): void {
    parent::processVariables();

    if (!$this->config->get('md-no-inline-code-wrap-vars')) {
      $this->variables = $this->processInlineCodeVars($this->variables, $this->config->get('md-inline-code-extra-file'));
    }

    if (!$this->config->get('md-no-inline-code-wrap-numbers')) {
      $this->variables = $this->processInlineCodeNumbers($this->variables);
    }

    if ($this->config->get('md-link-vars')) {
      $this->variables = $this->processLinks($this->variables);
    }
  }

  /**
   * Process inline code.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   A list of variables to process.
   * @param string[] $tokens
   *   Additional tokens to be processed as inline code.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
   *   A list of processed variables.
   */
  protected function processInlineCodeVars(array $variables, array $tokens = []): array {
    foreach ($variables as $variable) {
      $variable->setName('`' . $variable->getName() . '`');

      if (!empty($variable->getDefaultValue())) {
        $variable->setDefaultValue('`' . $variable->getDefaultValue() . '`');
      }

      $updated_paths = [];
      foreach ($variable->getPaths() as $path) {
        $updated_paths[] = '`' . $path . '`';
      }
      $variable->setPaths($updated_paths);

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
   * Process inline code to Convert numbers to code values.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   A list of variables to process.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
   *   A list of processed variables.
   */
  protected function processInlineCodeNumbers(array $variables): array {
    foreach ($variables as $variable) {
      $variable->setDescription(preg_replace('/\b((?<!`)[0-9]+)\b/', '`${1}`', $variable->getDescription()));
    }

    return $variables;
  }

  /**
   * Link variables.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   A list of variables to process.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
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

}

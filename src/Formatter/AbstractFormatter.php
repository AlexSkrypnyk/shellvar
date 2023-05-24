<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Config\ConfigAwareTrait;
use AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface;
use AlexSkrypnyk\ShellVariablesExtractor\Variable\VariableAwareTrait;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractFormatter.
 *
 * Abstract formatter class to be extended by all formatters.
 */
abstract class AbstractFormatter implements FormatterInterface, FactoryDiscoverableInterface {

  use ConfigAwareTrait;
  use VariableAwareTrait;

  /**
   * AbstractFormatter constructor.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
   *   The configuration.
   */
  public function __construct(Config $config) {
    $this->setConfig($config);
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleArguments(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions() {
    return [
      new InputOption(
        name: 'fields',
        mode: InputOption::VALUE_REQUIRED,
        description: 'Semicolon-separated list of fields. Each field is a key-label pair in the format "key=label". If label is omitted, key is used as label.',
        default: 'name=Name;default_value="Default value";description=Description'
      ),
      new InputOption(
        name: 'unset',
        mode: InputOption::VALUE_REQUIRED,
        description: 'A string to represent a value for variables that are defined but have no set value.',
        default: 'UNSET'
      ),
      new InputOption(
        name: 'sort',
        mode: InputOption::VALUE_NONE,
        description: 'Sort variables in ascending order by name.'
      ),
      new InputOption(
        name: 'path-strip-prefix',
        mode: InputOption::VALUE_REQUIRED,
        description: 'Strip the provided prefix from the path.',
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function processConfig(Config $config): void {
    $header = $config->get('fields');

    if ($header && !is_array($header)) {
      $pairs = explode(';', $header);
      $result = [];
      foreach ($pairs as $pair) {
        $parts = explode('=', $pair, 2);
        $result[$parts[0]] = trim($parts[1] ?? $parts[0], '"');
      }

      $config->set('fields', $result);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $variables): string {
    $this->setVariables($variables);

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
   * Process variables data before formatting.
   */
  protected function processVariables(): void {
    if ($this->config->get('sort')) {
      $this->variables = $this->processSort($this->variables);
    }

    if ($this->config->get('unset')) {
      $this->variables = $this->processUnset($this->variables, $this->config->get('unset'));
    }

    if ($this->config->get('path-strip-prefix')) {
      $this->variables = $this->processPathStripPrefix($this->variables, $this->config->get('path-strip-prefix'));
    }
  }

  /**
   * Process variables to sort.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   The variables array.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
   *   An array of processed variables.
   */
  protected function processSort($variables) {
    ksort($variables);

    return $variables;
  }

  /**
   * Process variables to set values for the variables without a value.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   The variables array.
   * @param string $unset
   *   The value to set for the variables without a value.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
   *   An array of processed variables.
   */
  protected function processUnset(array $variables, string $unset): array {
    foreach ($variables as $variable) {
      if (empty($variable->getDefaultValue())) {
        $variable->setDefaultValue($unset);
      }
    }

    return $variables;
  }

  /**
   * Process variables to set values for the variables without a value.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   The variables array.
   * @param string $prefix
   *   The prefix to strip from the path.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
   *   An array of processed variables.
   */
  protected function processPathStripPrefix(array $variables, string $prefix): array {
    foreach ($variables as $variable) {
      if (str_starts_with($variable->getPath(), $prefix)) {
        $variable->setPath(str_replace($prefix, '', $variable->getPath()));
      }
    }

    return $variables;
  }

}

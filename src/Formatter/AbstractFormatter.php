<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Formatter;

use AlexSkrypnyk\CsvTable\CsvTable;
use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Config\ConfigAwareTrait;
use AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface;
use AlexSkrypnyk\Shellvar\Variable\VariableAwareTrait;
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
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   The configuration.
   */
  public function __construct(Config $config) {
    $this->setConfig($config);
  }

  /**
   * Get console arguments.
   *
   * @return array<mixed>
   *   {@inheritdoc}
   */
  public static function getConsoleArguments(): array {
    return [];
  }

  /**
   * Returns Console Options.
   *
   * @return array<InputOption>
   *   {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
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
      new InputOption(
        name: 'column-order',
        mode: InputOption::VALUE_REQUIRED,
        description: 'Comma-separated list of column names to specify output order. Columns not specified are appended in their original order. Only applies to tabular formats (csv, md-table).',
      ),
      new InputOption(
        name: 'only-columns',
        mode: InputOption::VALUE_REQUIRED,
        description: 'Comma-separated list of column names to include. Only these columns will appear in output. Only applies to tabular formats (csv, md-table).',
      ),
      new InputOption(
        name: 'exclude-columns',
        mode: InputOption::VALUE_REQUIRED,
        description: 'Comma-separated list of column names to exclude from output. Only applies to tabular formats (csv, md-table).',
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function processConfig(Config $config): void {
    $header = $config->get('fields');

    if (!empty($header) && is_string($header)) {
      $pairs = explode(';', $header);

      $columns = [];
      foreach ($pairs as $pair) {
        $parts = explode('=', $pair, 2);
        $columns[$parts[0]] = trim($parts[1] ?? $parts[0], '"');
      }

      $config->set('fields', $columns);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $variables): string {
    $this->setVariables($variables);

    $this->processVariables();

    $formatted = $this->doFormat();

    return $this->postFormat($formatted);
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
      $unset_value = is_string($this->config->get('unset')) ? $this->config->get('unset') : 'UNSET';
      $this->variables = $this->processUnset($this->variables, $unset_value);
    }

    if ($this->config->get('path-strip-prefix')) {
      $path_strip_prefix = is_string($this->config->get('path-strip-prefix')) ? $this->config->get('path-strip-prefix') : '';
      if (!empty($path_strip_prefix)) {
        $this->variables = $this->processPathStripPrefix($this->variables, $path_strip_prefix);
      }
    }

    $this->variables = $this->processDescriptions($this->variables);
  }

  /**
   * Process variables to sort.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   The variables array.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   An array of processed variables.
   */
  protected function processSort($variables) {
    ksort($variables);

    return $variables;
  }

  /**
   * Process variables to set values for the variables without a value.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   The variables array.
   * @param string $unset
   *   The value to set for the variables without a value.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   An array of processed variables.
   */
  protected function processUnset(array $variables, string $unset): array {
    foreach ($variables as $variable) {
      if (is_null($variable->getDefaultValue())) {
        $variable->setDefaultValue($unset);
      }
    }

    return $variables;
  }

  /**
   * Process variables to set values for the variables without a value.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   The variables array.
   * @param string $prefix
   *   The prefix to strip from the path.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   An array of processed variables.
   */
  protected function processPathStripPrefix(array $variables, string $prefix): array {
    foreach ($variables as $variable) {
      $updated_paths = [];
      foreach ($variable->getPaths() as $path) {
        if (str_starts_with($path, $prefix)) {
          $path = str_replace($prefix, '', $path);
        }
        $updated_paths[] = $path;
      }
      $variable->setPaths($updated_paths);
    }

    return $variables;
  }

  /**
   * Process descriptions.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   A list of variables to process.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   A list of processed variables.
   */
  protected function processDescriptions(array $variables): array {
    foreach ($variables as $variable) {
      $description = $variable->getDescription();
      $description = $this->processDescription($description);
      $variable->setDescription($description);
    }

    return $variables;
  }

  /**
   * Process description.
   *
   * @param string $description
   *   A description to process.
   *
   * @return string
   *   A processed description.
   */
  protected function processDescription(string $description): string {
    $description = trim($description);

    // Replace multiple empty lines with a single one.
    $replaced_description = preg_replace('/(\n){3,}/', "\n\n", $description);

    return $replaced_description ?: $description;
  }

  /**
   * Post format the content.
   *
   * @param string $content
   *   The content to post format.
   *
   * @return string
   *   The post formatted content.
   */
  protected function postFormat(string $content): string {
    return implode(PHP_EOL, array_map(trim(...), explode(PHP_EOL, $content)));
  }

  /**
   * Apply column transformations to a CsvTable instance.
   *
   * @param \AlexSkrypnyk\CsvTable\CsvTable $csvTable
   *   The CsvTable instance.
   *
   * @return \AlexSkrypnyk\CsvTable\CsvTable
   *   The CsvTable instance with transformations applied.
   */
  protected function applyColumnTransformations(CsvTable $csvTable): CsvTable {
    // Apply onlyColumns filter.
    $onlyColumns = $this->config->get('only-columns');
    if (is_string($onlyColumns) && !empty($onlyColumns)) {
      $columns = array_map(trim(...), explode(',', $onlyColumns));
      $csvTable->onlyColumns($columns);
    }

    // Apply withoutColumns exclusion.
    $excludeColumns = $this->config->get('exclude-columns');
    if (is_string($excludeColumns) && !empty($excludeColumns)) {
      $columns = array_map(trim(...), explode(',', $excludeColumns));
      $csvTable->withoutColumns($columns);
    }

    // Apply columnOrder reordering.
    $columnOrder = $this->config->get('column-order');
    if (is_string($columnOrder) && !empty($columnOrder)) {
      $columns = array_map(trim(...), explode(',', $columnOrder));
      $csvTable->columnOrder($columns);
    }

    return $csvTable;
  }

}

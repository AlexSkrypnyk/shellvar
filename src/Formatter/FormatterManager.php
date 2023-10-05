<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

use AlexSkrypnyk\ShellVariablesExtractor\AbstractManager;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class FormatterManager.
 *
 * Manages the formatters.
 */
class FormatterManager extends AbstractManager {

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [
      new InputOption(
        name: 'format',
        mode: InputOption::VALUE_REQUIRED,
        description: 'The output format.',
        default: 'csv'
      ),
    ];
  }

  /**
   * Format provided variables using the format specified in config.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   The variables to format.
   *
   * @return string
   *   The formatted variables.
   */
  public function format($variables): string {
    $format = $this->config->get('format');
    $formatter = $this->factory->create($format, $this->config);

    // @phpstan-ignore-next-line
    return $formatter->format($variables);
  }

  /**
   * {@inheritdoc}
   */
  protected static function getDiscoveryDirectory(): string {
    return __DIR__ . '/../Formatter';
  }

}

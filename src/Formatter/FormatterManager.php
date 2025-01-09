<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Formatter;

use AlexSkrypnyk\Shellvar\AbstractManager;
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
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   The variables to format.
   *
   * @return string
   *   The formatted variables.
   */
  public function format(array $variables): string {
    $format = $this->config->get('format');

    if (!is_scalar($format)) {
      throw new \RuntimeException('No format specified.');
    }

    $format = (string) $format;

    $formatter = $this->factory->create($format, $this->config);

    if (!$formatter instanceof FormatterInterface) {
      throw new \RuntimeException('Invalid formatter.');
    }

    return $formatter->format($variables);
  }

  /**
   * {@inheritdoc}
   */
  protected static function getDiscoveryDirectory(): string {
    return __DIR__ . '/../Formatter';
  }

}

<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Formatter;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class CsvFormatter.
 *
 * Formats variables data as a CSV.
 */
class CsvFormatter extends AbstractFormatter {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'csv';
  }

  /**
   * Get console options.
   *
   * @return array<InputOption>
   *   Returns console options.
   *   {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return array_merge(parent::getConsoleOptions(), [
      new InputOption(
        name: 'csv-separator',
        mode: InputOption::VALUE_REQUIRED,
        description: 'Separator for the CSV output format.',
        default: ';'
      ),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function doFormat(): string {
    $file = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');

    if ($file === FALSE) {
      // @codeCoverageIgnoreStart
      throw new \RuntimeException('Failed to open temporary file.');
      // @codeCoverageIgnoreEnd
    }

    $header = $this->config->get('fields');
    $header = is_array($header) ? $header : [];
    $header = array_map(static fn($value): string => is_scalar($value) || $value === NULL ? strval($value) : '', $header);

    $separator = is_string($this->config->get('csv-separator', ',')) ? $this->config->get('csv-separator', ',') : ',';

    fputcsv($file, array_values($header), $separator, '"', '\\');

    foreach ($this->variables as $variable) {
      fputcsv($file, $variable->toArray(array_keys($header)), $separator, '"', '\\');
    }

    rewind($file);

    $content = stream_get_contents($file);
    if ($content === FALSE) {
      // @codeCoverageIgnoreStart
      throw new \RuntimeException('Failed to read temporary file.');
      // @codeCoverageIgnoreEnd
    }

    return $content;
  }

  /**
   * {@inheritdoc}
   */
  protected function processDescription(string $description): string {
    $description = parent::processDescription($description);

    // Remove a single new line.
    $replaced_description = preg_replace('/(?<!\n)\n(?!\n)/', ' ', $description);

    return $replaced_description ?: $description;
  }

}

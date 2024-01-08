<?php

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
    $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');

    $header = $this->config->get('fields');
    // @phpstan-ignore-next-line
    fputcsv($csv, array_values($header), $this->config->get('csv-separator') ?? ',');

    foreach ($this->variables as $variable) {
      // @phpstan-ignore-next-line
      fputcsv($csv, $variable->toArray(array_keys($header)), $this->config->get('csv-separator') ?? ',');
    }

    // @phpstan-ignore-next-line
    rewind($csv);
    // @phpstan-ignore-next-line
    return stream_get_contents($csv);
  }

  /**
   * Get process description.
   *
   * @return string|null
   *   Returns process description
   *   {@inheritdoc}
   */
  protected function processDescription(string $description): string|null {
    $description = parent::processDescription($description);

    // Remove a single new line.
    // @phpstan-ignore-next-line
    $description = preg_replace('/(?<!\n)\n(?!\n)/', ' ', $description);

    return $description;
  }

}

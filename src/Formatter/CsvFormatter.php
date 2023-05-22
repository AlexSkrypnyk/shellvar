<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

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
   * {@inheritdoc}
   */
  public function format(): string {
    $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');

    fputcsv($csv, ['Name', 'Default value', 'Description'], $this->config['csv-separator'] ?? ',');

    foreach ($this->variables as $variable) {
      fputcsv($csv, $variable->toArray(), $this->config['csv-separator'] ?? ',');
    }

    rewind($csv);

    return stream_get_contents($csv);
  }

}

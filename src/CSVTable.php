<?php

namespace Drevops\App;

/**
 * CSVTable.
 *
 * Credits: https://github.com/mre/CSVTable.
 *
 * Refactored and optimised to support linebreaks in cells and other
 * improvements.
 */
class CSVTable {

  /**
   * Initial CSV string.
   *
   * @var string
   */
  protected $csvString;

  /**
   * CSV delimiter.
   *
   * @var mixed|string
   */
  protected $csvDelimiter;

  /**
   * CSV enclosure.
   *
   * @var mixed|string
   */
  protected $csvEnclosure;

  /**
   * Table separator.
   *
   * @var mixed|string
   */
  protected $tableSeparator;

  /**
   * Table header.
   *
   * @var string
   */
  protected $header;

  /**
   * Table rows.
   *
   * @var string
   */
  protected $rows;

  /**
   * Table length.
   *
   * @var int
   */
  protected $length;

  /**
   * Array of column widths.
   *
   * @var array
   */
  protected $colWidths;

  /**
   * CSVTable constructor.
   *
   * @param string $csv_string
   *   CSV string.
   * @param string $delim
   *   CSV delimiter.
   * @param string $enclosure
   *   CSV enclosure.
   * @param string $table_separator
   *   Table separator.
   */
  public function __construct($csv_string, $delim = ',', $enclosure = '"', $table_separator = '|') {
    $this->csvString = $csv_string;
    $this->csvDelimiter = $delim;
    $this->csvEnclosure = $enclosure;
    $this->tableSeparator = $table_separator;

    // Fill the rows with Markdown output.
    // Table header.
    $this->header = '';
    // Table rows.
    $this->rows = '';
    $this->csvToTable();
  }

  /**
   * Convert the CSV string into a table.
   */
  private function csvToTable() {
    $parsed_array = $this->toArray($this->csvString);
    $this->length = $this->minRowLength($parsed_array);
    $this->colWidths = $this->maxColumnWidths($parsed_array);

    $header_array = array_shift($parsed_array);
    $this->header = $this->createHeader($header_array);
    $this->rows = $this->createRows($parsed_array);
  }

  /**
   * Convert the CSV into a PHP array.
   */
  public function toArray($csv) {
    $output = [];

    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $csv);
    rewind($stream);

    while (($data = fgetcsv($stream, 0, ";")) !== FALSE) {
      $output[] = $data;
    }

    fclose($stream);

    return $output;
  }

  /**
   * Create header.
   */
  private function createHeader($header_array) {
    return $this->createRow($header_array) . $this->createHeaderSeparator();
  }

  /**
   * Create header separator.
   */
  private function createHeaderSeparator() {
    $output = '';

    $output .= $this->tableSeparator;

    for ($i = 0; $i < $this->length - 1; $i++) {
      $output .= str_repeat('-', $this->colWidths[$i] + 2);
      $output .= $this->tableSeparator;
    }

    $last_index = $this->length - 1;
    $output .= str_repeat('-', $this->colWidths[$last_index] + 2);

    $output .= $this->tableSeparator;

    return $output . "\n";
  }

  /**
   * Create rows.
   */
  protected function createRows($rows) {
    $output = '';
    foreach ($rows as $row) {
      $output .= $this->createRow($row);
    }

    return $output;
  }

  /**
   * Add padding to a string.
   */
  private function padded($str, $width) {
    if ($width < strlen($str)) {
      return $str;
    }
    $padding_length = $width - strlen($str);
    $padding = str_repeat(" ", $padding_length);

    return $str . $padding;
  }

  /**
   * Create a row.
   */
  protected function createRow($row) {
    $output = '';

    $output .= $this->tableSeparator . ' ';

    // Only create as many columns as the minimal number of elements
    // in all rows. Otherwise this would not be a valid Markdown table.
    for ($i = 0; $i < $this->length - 1; ++$i) {
      $element = $this->padded($row[$i], $this->colWidths[$i]);
      $output .= $element;
      $output .= ' ' . $this->tableSeparator . ' ';
    }
    // Don't append a separator to the last element.
    $last_index = $this->length - 1;
    $element = $this->padded($row[$last_index], $this->colWidths[$last_index]);
    $output .= $element;

    $output .= ' ' . $this->tableSeparator;

    // Row ends with a newline.
    $output .= "\n";

    return $output;
  }

  /**
   * Calculate the minimum number of elements in all rows.
   */
  private function minRowLength($arr) {
    $min = PHP_INT_MAX;
    foreach ($arr as $row) {
      $row_length = count($row);
      if ($row_length < $min) {
        $min = $row_length;
      }
    }

    return $min;
  }

  /**
   * Calculate the maximum width of each column in characters.
   */
  private function maxColumnWidths($arr) {
    // Set all column widths to zero.
    $column_widths = array_fill(0, $this->length, 0);
    foreach ($arr as $row) {
      foreach ($row as $k => $v) {
        if ($column_widths[$k] < strlen($v)) {
          $column_widths[$k] = strlen($v);
        }
        if ($k == $this->length - 1) {
          // We don't need to look any further since these elements
          // will be dropped anyway because all table rows must have the
          // same length to create a valid Markdown table.
          break;
        }
      }
    }

    return $column_widths;
  }

  /**
   * Get the table markup.
   */
  public function getMarkup() {
    return $this->header . $this->rows;
  }

}

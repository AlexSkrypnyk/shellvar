<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

use AlexSkrypnyk\CsvTable\CsvTable;
use AlexSkrypnyk\CsvTable\Markdown;

/**
 * Class MarkdownTableFormatter.
 *
 * Formats variables data as a Markdown table.
 */
class MarkdownTableFormatter extends AbstractMarkdownFormatter {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'md-table';
  }

  /**
   * {@inheritdoc}
   */
  protected function doFormat(): string {
    // Use the CsvFormatter to format the variables data as CSV to then render
    // it as a CsvTable with Markdown renderer.
    // Also, using ';' as a CSV separator to make sure that the Markdown table
    // is rendered correctly.
    $csv = (new CsvFormatter($this->config, ';'))->format($this->variables);

    return (new CsvTable($csv, ';'))->render(Markdown::class);
  }

}

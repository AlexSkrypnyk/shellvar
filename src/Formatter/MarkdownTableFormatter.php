<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Formatter;

use AlexSkrypnyk\CsvTable\CsvTable;

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
  protected function processVariables(): void {
    parent::processVariables();

    // Add anchors to variable names when linking is enabled.
    if ($this->config->get('md-link-vars')) {
      $anchor_case = $this->config->get('md-link-vars-anchor-case');
      if (is_string($anchor_case)) {
        $this->variables = $this->addAnchors($this->variables, $anchor_case);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doFormat(): string {
    // Use the CsvFormatter to format the variables data as CSV to then render
    // it as a CsvTable with Markdown renderer.
    // Also, using ';' as a CSV separator to make sure that the Markdown table
    // is rendered correctly.
    // Note: Column transformations are already applied by CsvFormatter.
    $csv = (new CsvFormatter($this->config))->format($this->variables);

    return (new CsvTable($csv, ';'))->format('markdown_table');
  }

  /**
   * Add anchors to variable names for linking.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   A list of variables to process.
   * @param string $anchor_case
   *   Anchor case.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   A list of processed variables.
   */
  protected function addAnchors(array $variables, string $anchor_case = self::VARIABLE_LINK_CASE_PRESERVE): array {
    foreach ($variables as $variable) {
      $var_name = $variable->getName();
      // Remove backticks to get raw variable name.
      $raw_name = trim($var_name, '`');

      // Apply case transformation to match link anchor format.
      $anchor_name = match ($anchor_case) {
        self::VARIABLE_LINK_CASE_UPPER => strtoupper($raw_name),
        self::VARIABLE_LINK_CASE_LOWER => strtolower($raw_name),
        default => $raw_name,
      };

      // Prepend anchor tag before the variable name.
      $variable->setName('<a id="' . urlencode($anchor_name) . '"></a>' . $var_name);
    }

    return $variables;
  }

}

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
  protected function processDescription(string $description): string {
    // Start with base processing (trims and normalizes multiple newlines).
    $description = AbstractFormatter::processDescription($description);

    $br = '<br/>';
    $nl = "\n";

    $lines = explode($nl, $description);
    $separators = [];

    // Convert single newlines to spaces for regular text,
    // but preserve list structure with <br/> tags.
    // Double newlines become <br/><br/> since table cells can't have
    // actual newlines.
    $in_list = FALSE;
    foreach ($lines as $k => $line) {
      // Last line - no separator after it.
      if ($k === count($lines) - 1) {
        $separators[$k] = '';
        continue;
      }

      $next_line = $lines[$k + 1] ?? '';

      // Current line is empty (from double newline) - skip it,
      // previous line handles the spacing.
      if (empty($line)) {
        $separators[$k] = '';
        $in_list = FALSE;
      }
      elseif ($this->isListItem($line) && empty($next_line)) {
        // List item followed by empty line (double newline) - use <br/><br/>.
        $separators[$k] = $br . $br;
        $in_list = TRUE;
      }
      elseif ($this->isListItem($line)) {
        $separators[$k] = $br;
        $in_list = TRUE;
      }
      elseif ($in_list) {
        // List continuation line - trim it and add <br/>.
        $lines[$k] = trim($line);
        if (empty($next_line)) {
          // Before double newline.
          $separators[$k] = $br . $br;
        }
        else {
          $separators[$k] = $br;
        }
        $in_list = TRUE;
      }
      elseif ($this->isListItem($next_line)) {
        // Line before a list item should have <br/> to start the list.
        $separators[$k] = $br;
        $in_list = FALSE;
      }
      elseif (empty($next_line)) {
        // Regular text before double newline - no trailing space.
        $separators[$k] = $br . $br;
        $in_list = FALSE;
      }
      else {
        // For regular text, convert single newlines to spaces.
        $separators[$k] = ' ';
        $in_list = FALSE;
      }
    }

    // Rebuild description with separators.
    $result = '';
    foreach ($lines as $k => $line) {
      $result .= $line . ($separators[$k] ?? '');
    }

    return $result;
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

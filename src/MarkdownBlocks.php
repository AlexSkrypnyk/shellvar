<?php

namespace Drevops\App;

/**
 * CSVBlock.
 */
class MarkdownBlocks {

  /**
   * Array of variables.
   *
   * @var array
   */
  protected $variables;

  /**
   * Template.
   *
   * @var string
   */
  protected $template;

  /**
   * Processed markup.
   *
   * @var string
   */
  protected $markup;

  /**
   * Constructor.
   *
   * @param array $variables
   *   Array of variables.
   * @param string $template
   *   Template string.
   */
  public function __construct(array $variables, $template) {
    $this->variables = $variables;
    $this->template = $template;
    $this->markup = $this->csvToBlock();
  }

  /**
   * Convert CSV to block.
   *
   * @return string
   *   Block.
   */
  protected function csvToBlock() {
    $content = '';

    foreach ($this->variables as $item) {
      $placeholders_tokens = array_map(function ($v) {
        return '{{ ' . $v . ' }}';
      }, array_keys($item));

      $placeholders_values = array_map(function ($v) {
        return str_replace("\n", "<br/>", $v);
      }, $item);

      $placeholders = array_combine($placeholders_tokens, $placeholders_values);
      $content .= str_replace("\n\n\n", "\n", strtr($this->template, $placeholders));
    }

    return $content;
  }

  /**
   * Convert CSV to table.
   */
  public function toArray($csv) {
    $array = [];
    // Parse the rows.
    $parsed = str_getcsv($csv, "\n");
    foreach ($parsed as &$row) {
      // Parse the items in rows.
      $row = str_getcsv($row, $this->delim, $this->enclosure);
      array_push($array, $row);
    }

    return $array;
  }

  /**
   * Get markup.
   *
   * @return string
   *   Markup.
   */
  public function getMarkup() {
    return $this->markup;
  }

}

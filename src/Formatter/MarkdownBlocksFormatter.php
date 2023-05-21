<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

/**
 * Class MarkdownBlocksFormatter.
 *
 * Format variables data as a Markdown blocks.
 */
class MarkdownBlocksFormatter extends AbstractMarkdownFormatter {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'md-blocks';
  }

  /**
   * {@inheritdoc}
   */
  public function doFormat(): string {
    $content = '';

    foreach ($this->variables as $variable) {
      $placeholders_tokens = array_map(function ($v) {
        return '{{ ' . $v . ' }}';
      }, array_keys($variable->toArray()));

      $placeholders_values = array_map(function ($v) {
        return str_replace("\n", "<br />", $v);
      }, $variable->toArray());

      $placeholders = array_combine($placeholders_tokens, $placeholders_values);
      $content .= str_replace("\n\n\n", "\n", strtr(file_get_contents($this->config['md-block-template-file']), $placeholders));
    }

    return $content;
  }

}

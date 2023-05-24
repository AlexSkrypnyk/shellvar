<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

use AlexSkrypnyk\ShellVariablesExtractor\Utils;
use Symfony\Component\Console\Input\InputOption;

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
  public static function getConsoleOptions() {
    return array_merge(parent::getConsoleOptions(), [
      new InputOption(
        name: 'md-block-template-file',
        mode: InputOption::VALUE_REQUIRED,
        description: "A path to a Markdown template file used for Markdown blocks (md-blocks) output format.\n{{ name }}, {{ description }}, {{ default_value }} and {{ path }} tokens can be used within the template."
      ),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function processConfig($config): void {
    parent::processConfig($config);

    if (!empty($config->get('md-block-template-file'))) {
      $config->set('md-block-template-file', file_get_contents(Utils::resolvePath($config->get('md-block-template-file'))));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doFormat(): string {
    $content = '';

    $fields = [
      'name',
      'default_value',
      'description',
      'path',
    ];

    $template = $this->config->get('md-block-template-file') ?: static::getDefaultTemplate();

    foreach ($this->variables as $variable) {
      $placeholders_tokens = array_map(function ($v) {
        return '{{ ' . $v . ' }}';
      }, array_keys($variable->toArray($fields)));

      $placeholders_values = array_map(function ($v) {
        return str_replace("\n", "<br />", $v);
      }, $variable->toArray($fields));

      $placeholders = array_combine($placeholders_tokens, $placeholders_values);
      $content .= str_replace("\n\n\n", "\n", strtr($template, $placeholders));
    }

    return $content;
  }

  /**
   * Default template for a single block.
   *
   * @return string
   *   The template.
   */
  protected static function getDefaultTemplate() {
    return <<<EOT
    ### {{ name }}
    
    {{ description }}
    
    Default value: {{ default_value }}


    EOT;
  }

}

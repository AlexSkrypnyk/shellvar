<?php

namespace AlexSkrypnyk\Shellvar\Formatter;

use AlexSkrypnyk\Shellvar\Utils;
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
   * Get console options.
   *
   * @return array<InputOption>
   *   Returns array of console options
   *   {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
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
  public function processConfig(mixed $config): void {
    parent::processConfig($config);
    if (!empty($config->get('md-block-template-file'))) {
      // @phpstan-ignore-next-line
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
      'paths',
    ];

    $template = $this->config->get('md-block-template-file') ?: static::getDefaultTemplate();

    foreach ($this->variables as $variable) {
      $placeholders_tokens = array_map(function ($v) {
        return '{{ ' . $v . ' }}';
      }, array_keys($variable->toArray($fields)));

      $placeholders_values = $variable->toArray($fields);

      $placeholders = array_combine($placeholders_tokens, $placeholders_values);
      // @phpstan-ignore-next-line
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

<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Filter;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Utils;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExcludeFromFileFilter.
 *
 * Filter out excluded variables from a file.
 */
class ExcludeFromFileFilter extends AbstractFilter {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'filter-exclude-from-file';
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [
      new InputOption(
        name: 'exclude-file',
        mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        description: 'A path to a file that contains variables to be excluded from the extraction process.',
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function processConfig(Config $config):void {
    parent::processConfig($config);
    $config->set('exclude-file', Utils::getNonEmptyLinesFromFiles(Utils::resolvePaths($config->get('exclude-file', []))));
  }

  /**
   * {@inheritdoc}
   */
  public function filter(array $variables): array {
    return array_diff_key($variables, array_flip($this->config->get('exclude-file')));
  }

}

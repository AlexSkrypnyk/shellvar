<?php

namespace AlexSkrypnyk\Shellvar\Filter;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Utils;
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
        name: 'exclude-from-file',
        mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        description: 'A path to a file that contains variables to be excluded from the extraction process.',
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function processConfig(Config $config): void {
    parent::processConfig($config);

    $files = $config->get('exclude-from-file', []);
    $files = is_array($files) ? $files : [$files];
    $config->set('exclude-from-file', Utils::getNonEmptyLinesFromFiles(Utils::resolvePaths($files)));
  }

  /**
   * {@inheritdoc}
   */
  public function filter(array $variables): array {
    $files = $this->config->get('exclude-from-file');
    $files = is_array($files) ? $files : [$files];

    return array_diff_key($variables, array_flip($files));
  }

}

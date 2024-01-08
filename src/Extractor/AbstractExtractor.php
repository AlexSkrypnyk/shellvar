<?php

namespace AlexSkrypnyk\Shellvar\Extractor;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Config\ConfigAwareTrait;
use AlexSkrypnyk\Shellvar\ConsoleAwareInterface;
use AlexSkrypnyk\Shellvar\Utils;
use AlexSkrypnyk\Shellvar\Variable\VariableAwareTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractExtractor.
 *
 * Provides generic functionality for all extractors.
 */
abstract class AbstractExtractor implements ExtractorInterface, ConsoleAwareInterface {

  use ConfigAwareTrait;
  use VariableAwareTrait;

  /**
   * AbstractExtractor constructor.
   *
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   Config.
   */
  public function __construct(Config $config) {
    $this->setConfig($config);
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleArguments(): array {
    return [
      new InputArgument(
        name: 'paths',
        mode: InputArgument::IS_ARRAY | InputArgument::REQUIRED,
        description: 'File or directory to scan. Multiple files separated by space.',
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [
      new InputOption(
        name: 'skip-text',
        mode: InputOption::VALUE_REQUIRED,
        description: 'Skip variable extraction if the comment has this specified text.',
        default: '@skip'
      ),
      new InputOption(
        name: 'skip-description-prefix',
        mode: InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
        description: 'Skip description lines that start with the provided prefix.',
        default: []
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function processConfig(Config $config): void {
    // @phpstan-ignore-next-line
    $config->set('paths', $this->scanPaths(Utils::resolvePaths($config->get('paths', []))));
    $config->set('skip-text', $config->get('skip-text', '@skip'));
  }

  /**
   * {@inheritdoc}
   */
  public function extract(): array {
    // @phpstan-ignore-next-line
    foreach ($this->config->get('paths') as $path) {
      $this->extractVariablesFromFile($path);
    }

    return $this->variables;
  }

  /**
   * Extract variables from a single file.
   *
   * If cross-file extraction is required - override extract() method.
   *
   * @param string|mixed $file
   *   Path to file.
   */
  abstract protected function extractVariablesFromFile(mixed $file): void;

  /**
   * Get a list of files to scan.
   *
   * @param array<string> $paths
   *   A list of paths to scan.
   *
   * @return array<string>
   *   A list of files to scan.
   */
  protected function scanPaths($paths) : array {
    $files = [];

    $paths = Utils::resolvePaths($paths);

    foreach ($paths as $path) {
      if (is_file($path)) {
        $files[] = $path;
      }
      else {
        if (is_readable($path . '/.env')) {
          $files[] = $path . '/.env';
        }
        // @phpstan-ignore-next-line
        $files = array_merge($files, glob($path . '/.*.{bash,sh}', GLOB_BRACE), glob($path . '/*.{bash,sh}', GLOB_BRACE));
      }
    }

    return $files;
  }

}

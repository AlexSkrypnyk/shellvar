<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Extractor;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Config\ConfigAwareTrait;
use AlexSkrypnyk\ShellVariablesExtractor\ConsoleAwareInterface;
use AlexSkrypnyk\ShellVariablesExtractor\Utils;
use AlexSkrypnyk\ShellVariablesExtractor\Variable\VariableAwareTrait;
use Symfony\Component\Console\Input\InputArgument;

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
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
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
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function processConfig(Config $config): void {
    $config->set('paths', $this->scanPaths(Utils::resolvePaths($config->get('paths', []))));
  }

  /**
   * {@inheritdoc}
   */
  public function extract(): array {
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
   * @param string $file
   *   Path to file.
   */
  abstract protected function extractVariablesFromFile(string $file): void;

  /**
   * Get a list of files to scan.
   *
   * @param array $paths
   *   A list of paths to scan.
   *
   * @return array
   *   A list of files to scan.
   */
  protected function scanPaths($paths) {
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
        $files = array_merge($files, glob($path . '/*.{bash,sh}', GLOB_BRACE));
      }
    }

    return $files;
  }

}

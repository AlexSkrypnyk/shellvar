<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Filter;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Config\ConfigAwareTrait;
use AlexSkrypnyk\ShellVariablesExtractor\ConsoleAwareInterface;

/**
 * Class AbstractFilter.
 *
 * Provides generic functionality for all filters.
 */
abstract class AbstractFilter implements FilterInterface, ConsoleAwareInterface {

  use ConfigAwareTrait;

  /**
   * AbstractFilter constructor.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
   *   The configuration.
   */
  public function __construct(Config $config) {
    $this->setConfig($config);
  }

  /**
   * {@inheritdoc}
   */
  public static function getPriority():int {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleArguments(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [];
  }

}

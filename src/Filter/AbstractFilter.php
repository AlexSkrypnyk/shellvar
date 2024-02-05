<?php

namespace AlexSkrypnyk\Shellvar\Filter;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Config\ConfigAwareTrait;
use AlexSkrypnyk\Shellvar\ConsoleAwareInterface;

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
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   The configuration.
   */
  public function __construct(Config $config) {
    $this->setConfig($config);
  }

  /**
   * {@inheritdoc}
   */
  public static function getPriority(): int {
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

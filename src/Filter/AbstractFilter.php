<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Filter;

use DrevOps\Shellvar\Config\Config;
use DrevOps\Shellvar\Config\ConfigAwareTrait;
use DrevOps\Shellvar\ConsoleAwareInterface;

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
   * @param \DrevOps\Shellvar\Config\Config $config
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

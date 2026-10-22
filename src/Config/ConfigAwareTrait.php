<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Config;

/**
 * Trait ConfigAwareTrait.
 *
 * Provides functionality for classes that need to be aware of the
 * configuration.
 */
trait ConfigAwareTrait {

  /**
   * The configuration.
   *
   * @var \DrevOps\Shellvar\Config\Config
   */
  protected $config;

  /**
   * Get the configuration.
   *
   * @return \DrevOps\Shellvar\Config\Config
   *   The configuration.
   */
  public function getConfig(): Config {
    return $this->config;
  }

  /**
   * Set the configuration.
   *
   * @param \DrevOps\Shellvar\Config\Config $config
   *   The configuration to set.
   */
  public function setConfig(Config $config): void {
    $this->processConfig($config);

    $this->config = $config;
  }

  /**
   * Process the configuration.
   *
   * @param \DrevOps\Shellvar\Config\Config $config
   *   The configuration to process.
   */
  protected function processConfig(Config $config): void {
    // Intentionally left empty.
  }

}

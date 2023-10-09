<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Config;

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
   * @var \AlexSkrypnyk\ShellVariablesExtractor\Config\Config
   */
  protected $config;

  /**
   * Get the configuration.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Config\Config
   *   The configuration.
   */
  public function getConfig(): Config {
    return $this->config;
  }

  /**
   * Set the configuration.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
   *   The configuration to set.
   */
  public function setConfig(Config $config): void {
    $this->processConfig($config);

    $this->config = $config;
  }

  /**
   * Process the configuration.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
   *   The configuration to process.
   *
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  protected function processConfig(Config $config): void {
    // Intentionally left empty.
  }

}

<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Config;

/**
 * Interface ConfigInterface.
 *
 * Interface for all configuration classes.
 */
interface ConfigInterface {

  /**
   * Get a configuration value.
   */
  public function get($name): mixed;

  /**
   * Set a configuration value.
   */
  public function set($name, $value): ConfigInterface;

}

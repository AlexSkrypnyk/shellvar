<?php

namespace AlexSkrypnyk\Shellvar\Config;

/**
 * Interface ConfigInterface.
 *
 * Interface for all configuration classes.
 */
interface ConfigInterface {

  /**
   * Get a configuration value.
   *
   * @param string $name
   *   Name.
   */
  public function get($name): mixed;

  /**
   * Set a configuration value.
   *
   * @param string $name
   *   Config name.
   * @param string|array|mixed $value
   *   Config value.
   */
  public function set($name, $value): ConfigInterface;

}

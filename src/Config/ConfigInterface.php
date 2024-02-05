<?php

namespace AlexSkrypnyk\Shellvar\Config;

/**
 * Interface ConfigInterface.
 *
 * Interface for all configuration classes.
 */
interface ConfigInterface {

  /**
   * Get config value.
   *
   * @param string $name
   *   Config name.
   * @param mixed $default
   *   Config value.
   *
   * @return mixed
   *   Config value.
   */
  public function get(string $name, mixed $default = NULL): mixed;

  /**
   * Set a configuration value.
   *
   * @param string $name
   *   Config name.
   * @param mixed $value
   *   Config value.
   */
  public function set(string $name, mixed $value): ConfigInterface;

}

<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Config;

/**
 * Class Config.
 *
 * Provides configuration for the application.
 */
class Config implements ConfigInterface {

  /**
   * The configuration values.
   *
   * @var array
   */
  protected $values = [];

  /**
   * Config constructor.
   */
  public function __construct(...$arguments) {
    $this->values = array_merge(...$arguments);
  }

  /**
   * {@inheritdoc}
   */
  public function get($name, $default = NULL): mixed {
    return $this->values[$name] ?? $default;
  }

  /**
   * {@inheritdoc}
   */
  public function set($name, $value): ConfigInterface {
    $this->values[$name] = $value;

    return $this;
  }

  /**
   * Set values from all arguments.
   */
  public function setAll(...$arguments) {
    $this->values = array_merge(...$arguments);
  }

}

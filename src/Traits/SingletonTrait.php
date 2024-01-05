<?php

namespace AlexSkrypnyk\Shellvar\Traits;

/**
 * Trait SingletonTrait.
 *
 * Provides functionality for classes that need to be singletons.
 */
trait SingletonTrait {

  /**
   * Hold the class instances.
   *
   * @var array<object>
   */
  protected static $instances = [];

  /**
   * {@inheritdoc}
   */
  public static function getInstance(...$args) {
    $cls = static::class;

    if (!isset(self::$instances[$cls])) {
      self::$instances[$cls] = new static(...$args);
    }

    return self::$instances[$cls];
  }

  /**
   * Reset the instance.
   */
  public static function resetInstance() : void {
    $cls = static::class;

    if (isset(self::$instances[$cls])) {
      unset(self::$instances[$cls]);
    }
  }

}

<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Traits;

/**
 * Interface SingletonInterface.
 *
 * Provides functionality for classes that need to be singletons.
 */
interface SingletonInterface {

  /**
   * Get the singleton instance.
   */
  public static function getInstance(...$args);

}

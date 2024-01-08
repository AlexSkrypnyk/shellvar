<?php

namespace AlexSkrypnyk\Shellvar\Factory;

/**
 * Interface FactoryDiscoverableInterface.
 *
 * Should be added to all classes that should be discovered by the factory.
 */
interface FactoryDiscoverableInterface {

  /**
   * Get entity name.
   *
   * @return string
   *   Entity name.
   */
  public static function getName(): string;

}

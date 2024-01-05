<?php

namespace AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery1;

use AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface;

/**
 * Class DummyDiscoverable11.
 *
 * Dummy discoverable class.
 */
class DummyDiscoverable11 implements FactoryDiscoverableInterface {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'DummyDiscoverable11';
  }

}

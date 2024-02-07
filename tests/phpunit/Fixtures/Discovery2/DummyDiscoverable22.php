<?php

namespace AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery2;

use AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface;

/**
 * Class DummyDiscoverable22.
 *
 * Dummy discoverable class.
 */
class DummyDiscoverable22 implements FactoryDiscoverableInterface {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'DummyDiscoverable22';
  }

}

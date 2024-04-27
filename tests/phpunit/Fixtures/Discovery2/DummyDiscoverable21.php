<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery2;

use AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface;

/**
 * Class DummyDiscoverable21.
 *
 * Dummy discoverable class.
 */
class DummyDiscoverable21 implements FactoryDiscoverableInterface {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'DummyDiscoverable21';
  }

}

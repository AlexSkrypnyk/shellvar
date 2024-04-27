<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery1;

use AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface;

/**
 * Class DummyDiscoverable12.
 *
 * Dummy discoverable class.
 */
class DummyDiscoverable12 implements FactoryDiscoverableInterface {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'DummyDiscoverable12';
  }

}

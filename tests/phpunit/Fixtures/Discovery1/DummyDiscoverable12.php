<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Fixtures\Discovery1;

use DrevOps\Shellvar\Factory\FactoryDiscoverableInterface;

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

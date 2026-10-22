<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Fixtures\Discovery2;

use DrevOps\Shellvar\Factory\FactoryDiscoverableInterface;

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

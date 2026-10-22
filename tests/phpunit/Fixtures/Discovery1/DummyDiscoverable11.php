<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Fixtures\Discovery1;

use DrevOps\Shellvar\Factory\FactoryDiscoverableInterface;

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

<?php

namespace AlexSkrypnyk\Tests\Fixtures\Discovery1;

use AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface;

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

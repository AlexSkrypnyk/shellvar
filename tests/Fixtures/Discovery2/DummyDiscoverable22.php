<?php

namespace AlexSkrypnyk\Tests\Fixtures\Discovery2;

use AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface;

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

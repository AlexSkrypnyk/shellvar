<?php

namespace AlexSkrypnyk\Tests\Fixtures\Discovery2;

use AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface;

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

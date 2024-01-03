<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Tests\Fixtures\Discovery1;

use AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface;

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

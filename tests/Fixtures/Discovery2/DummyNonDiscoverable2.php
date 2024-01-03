<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Tests\Fixtures\Discovery2;

/**
 * Class DummyNonDiscoverable2.
 *
 * Dummy non-discoverable class.
 */
class DummyNonDiscoverable2 {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'DummyNonDiscoverable2';
  }

}

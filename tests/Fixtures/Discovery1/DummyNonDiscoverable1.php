<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Tests\Fixtures\Discovery1;

/**
 * Class DummyNonDiscoverable.
 *
 * Dummy non-discoverable class.
 */
class DummyNonDiscoverable1 {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'DummyNonDiscoverable1';
  }

}

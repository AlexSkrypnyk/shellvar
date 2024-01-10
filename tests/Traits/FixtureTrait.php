<?php

namespace AlexSkrypnyk\Shellvar\Tests\Traits;

/**
 * Trait AssertTrait.
 *
 * Provides custom assertions.
 */
trait FixtureTrait {

  /**
   * Get path to a fixture file.
   */
  protected static function fixtureFile(string $filename): string {
    $path = static::fixtureDir() . DIRECTORY_SEPARATOR . $filename;
    if (!is_readable($path)) {
      throw new \RuntimeException(sprintf('Unable to find fixture file %s.', $path));
    }

    return $path;
  }

  /**
   * Get path to a fixture directory.
   */
  protected static function fixtureDir(): string {
    return 'tests/Fixtures';
  }

}

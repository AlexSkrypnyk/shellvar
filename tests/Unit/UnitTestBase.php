<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Tests\Unit;

use AlexSkrypnyk\ShellVariablesExtractor\Tests\Traits\MockTrait;
use AlexSkrypnyk\ShellVariablesExtractor\Tests\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class UnitTestBase.
 *
 * Base class to unit tests.
 */
abstract class UnitTestBase extends TestCase {

  use MockTrait;
  use ReflectionTrait;

  /**
   * Get path to a fixture file.
   */
  protected static function fixtureFile(string $filename) : string {
    $path = static::fixtureDir() . DIRECTORY_SEPARATOR . $filename;
    if (!is_readable($path)) {
      throw new \RuntimeException(sprintf('Unable to find fixture file %s.', $path));
    }

    return $path;
  }

  /**
   * Get path to a fixture directory.
   */
  protected static function fixtureDir() : string {
    return 'tests/Fixtures';
  }

}

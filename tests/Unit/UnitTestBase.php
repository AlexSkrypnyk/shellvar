<?php

namespace AlexSkrypnyk\Tests\Unit;

use AlexSkrypnyk\Tests\Traits\HelperTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class UnitTestBase.
 *
 * Base class to unit tests.
 */
abstract class UnitTestBase extends TestCase {

  use HelperTrait;

  /**
   * Get path to a fixture file.
   */
  protected function fixtureFile($filename) {
    $path = $this->fixtureDir() . DIRECTORY_SEPARATOR . $filename;
    if (!is_readable($path)) {
      throw new \RuntimeException(sprintf('Unable to find fixture file %s.', $path));
    }

    return $path;
  }

  /**
   * Get path to a fixture directory.
   */
  protected function fixtureDir() {
    return 'tests/fixtures';
  }

}

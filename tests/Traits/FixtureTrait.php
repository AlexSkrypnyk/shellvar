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

  /**
   * Create temp file from fixture file.
   *
   * @param string $fixture_file_name
   *   Fixture file name.
   *
   * @return string
   *   Temp file.
   *
   * @throws \Exception
   */
  protected static function createTempFileFromFixtureFile(string $fixture_file_name): string {
    // Create temp file.
    $file = tempnam(sys_get_temp_dir(), 'temp-fixture');
    if ($file === FALSE) {
      throw new \Exception('Unable create temp file from fixture file.');
    }

    // Copy content from fixture file to temp file.
    $fixture_file_content = file_get_contents(static::fixtureFile($fixture_file_name));
    if ($fixture_file_content === FALSE) {
      throw new \Exception('Unable copy content from fixture file to temp file.');
    }
    $result = file_put_contents($file, $fixture_file_content);
    if ($result === FALSE) {
      throw new \Exception('Unable copy content from fixture file to temp file.');
    }

    return $file;
  }

}

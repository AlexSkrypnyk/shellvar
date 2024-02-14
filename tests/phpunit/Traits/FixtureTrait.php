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
    return 'tests/phpunit/Fixtures';
  }

  /**
   * Create temp file from fixture file.
   *
   * @param string $fixture_file_name
   *   Fixture file name.
   * @param string $parent
   *   Parent directory.
   *
   * @return string
   *   Temp file.
   *
   * @throws \Exception
   */
  protected function createTempFileFromFixtureFile(string $fixture_file_name, string $parent = ''): string {
    if (!empty($parent)) {
      $parent = rtrim($parent, DIRECTORY_SEPARATOR);
    }

    $parent = sys_get_temp_dir() . DIRECTORY_SEPARATOR . getmypid() . DIRECTORY_SEPARATOR . $this->name() . DIRECTORY_SEPARATOR . $parent;

    mkdir($parent, 0777, TRUE);

    $file = $parent . DIRECTORY_SEPARATOR . $fixture_file_name;

    // Copy content from fixture file to temp file.
    $fixture_file_content = file_get_contents(static::fixtureFile($fixture_file_name));
    if ($fixture_file_content === FALSE) {
      throw new \Exception('Unable copy content from fixture file to temp file.');
    }

    $result = file_put_contents($file, $fixture_file_content);
    if ($result === FALSE) {
      throw new \Exception('Unable copy content from fixture file to temp file.');
    }

    $file = realpath($file);

    if (!$file) {
      throw new \Exception('Unable to resolve the real path for the temp file.');
    }

    return $file;
  }

}

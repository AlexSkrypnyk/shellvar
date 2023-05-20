<?php

namespace AlexSkrypnyk\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Class ScriptUnitTestBase.
 *
 * Base class to unit tests scripts.
 *
 * @group scripts
 */
abstract class UnitTestBase extends TestCase {

  /**
   * Script to include.
   *
   * @var string
   */
  protected $script = 'bin/shell-variables-extractor';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->validateScript();

    putenv('SCRIPT_RUN_SKIP=1');
    putenv('SCRIPT_QUIET=1');

    require_once $this->script;
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    parent::tearDown();
    if (!empty($this->tmpDir)) {
      @unlink($this->tmpDir);
    }
  }

  /**
   * Run script with optional arguments.
   *
   * @param array $args
   *   Optional array of arguments to pass to the script.
   * @param bool $verbose
   *   Optional flag to enable verbose output in the script.
   * @param bool $should_run
   *   Optional flag to enable running the script.
   *
   * @return array
   *   Array with the following keys:
   *   - code: (int) Exit code.
   *   - output: (string) Output.
   */
  protected function runScript(array $args = [], $verbose = FALSE, $should_run = TRUE) {
    if ($should_run) {
      putenv('SCRIPT_RUN_SKIP=0');
    }

    if ($verbose) {
      putenv('SCRIPT_QUIET=0');
    }

    $command = sprintf('php %s %s', $this->script, implode(' ', $args));
    $output = [];
    $result_code = 1;
    exec($command, $output, $result_code);

    return [
      'code' => $result_code,
      'output' => implode(PHP_EOL, $output),
    ];
  }

  /**
   * Get path to a fixture directory.
   */
  protected function fixtureDir() {
    return 'tests/phpunit/fixtures';
  }

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
   * Validate that the script file is readable.
   */
  protected function validateScript() {
    if (!is_readable($this->script)) {
      throw new \RuntimeException(sprintf('Unable to include script file %s.', $this->script));
    }
  }

}

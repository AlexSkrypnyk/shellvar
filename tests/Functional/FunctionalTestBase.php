<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Tests\Functional;

use AlexSkrypnyk\ShellVariablesExtractor\Tests\Unit\UnitTestBase;

/**
 * Class FunctionalTestBase.
 *
 * Base class for functional tests.
 */
abstract class FunctionalTestBase extends UnitTestBase {

  /**
   * Script to include.
   *
   * @var string
   */
  protected $script = 'shell-variables-extractor';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->validateScript();
  }

  /**
   * Validate that the script file is readable.
   */
  protected function validateScript(): void {
    if (!is_readable($this->script)) {
      throw new \RuntimeException(sprintf('Unable to include script file %s.', $this->script));
    }
  }

  /**
   * Run script with optional arguments.
   *
   * @param array $args
   *   Optional array of arguments to pass to the script.
   * @param bool $verbose
   *   Optional flag to enable verbose output in the script.
   *
   * @return array
   *   Array with the following keys:
   *   - code: (int) Exit code.
   *   - output: (string) Output.
   */
  protected function runScript(array $args = [], $verbose = FALSE): array {
    if ($verbose) {
      $args[] = '--verbose';
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

}

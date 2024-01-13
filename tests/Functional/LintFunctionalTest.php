<?php

declare(strict_types = 1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\Command\LintCommand;
use AlexSkrypnyk\Shellvar\Tests\Traits\FixtureTrait;

/**
 * Test Lint Command functional.
 *
 * @covers \AlexSkrypnyk\Shellvar\Command\LintCommand
 */
class LintFunctionalTest extends FunctionalTestCase {

  use FixtureTrait;

  /**
   * Test LintCommand.
   *
   * @throws \Exception
   */
  public function testLintCommand(): void {
    $command = new LintCommand();
    // No existing file.
    $output = $this->runExecute($command, ['file' => 'no-existing-file.sh']);
    $this->assertEquals('Could not open file no-existing-file.sh' . PHP_EOL, implode(PHP_EOL, $output));
    // Valid file.
    $valid_file = $this->createTempFileFromFixtureFile('wrapped.sh');
    $valid_file_not_run = $this->createTempFileFromFixtureFile('wrapped.sh');
    $this->assertFileEquals($valid_file, $valid_file_not_run);
    $output = $this->runExecute($command, ['file' => $valid_file]);
    $this->assertEquals("Found 0 variables in file \"$valid_file\" that are not wrapped in \${}." . PHP_EOL, implode(PHP_EOL, $output));
    $output = $this->runExecute($command, ['file' => $valid_file, '-f' => TRUE]);
    $this->assertEquals("Replaced 0 variables in file \"$valid_file\"." . PHP_EOL, implode(PHP_EOL, $output));
    $this->assertFileEquals($valid_file, $valid_file_not_run);
    // Invalid file.
    $invalid_file = $this->createTempFileFromFixtureFile('unwrapped.sh');
    $invalid_file_not_run = $this->createTempFileFromFixtureFile('unwrapped.sh');
    $this->assertFileEquals($invalid_file, $invalid_file_not_run);
    $output = $this->runExecute($command, ['file' => $invalid_file]);
    $this->assertEquals([
      '11: var=$VAR1',
      '12: var="$VAR2"',
      '14: var=$VAR3',
      "Found 3 variables in file \"$invalid_file\" that are not wrapped in \${}.",
      '',
    ], $output);
    $output = $this->runExecute($command, ['file' => $invalid_file, '-f' => TRUE]);
    $this->assertEquals([
      'Replaced in line 11: var=$VAR1',
      'Replaced in line 12: var="$VAR2"',
      'Replaced in line 14: var=$VAR3',
      "Replaced 3 variables in file \"$invalid_file\".",
      '',
    ], $output);
    $this->assertFileNotEquals($invalid_file, $invalid_file_not_run);
  }

}

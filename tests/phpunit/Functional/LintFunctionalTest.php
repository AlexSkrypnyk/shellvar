<?php

declare(strict_types=1);

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
  public function testLintCommandFile(): void {
    $command = new LintCommand();

    // Not existing file.
    $output = $this->runExecute($command, ['path' => 'no-existing-file.sh']);
    $this->assertEquals('Could not open file no-existing-file.sh' . PHP_EOL, implode(PHP_EOL, $output));
    $this->assertEquals(1, $this->commandTester->getStatusCode());

    // Valid file.
    $valid_file = $this->createTempFileFromFixtureFile('wrapped.sh');
    $valid_file_not_run = $this->createTempFileFromFixtureFile('wrapped.sh');
    $this->assertFileEquals($valid_file, $valid_file_not_run);

    $output = $this->runExecute($command, ['path' => $valid_file]);
    $this->assertEquals(0, $this->commandTester->getStatusCode());
    $this->assertEquals(sprintf('Found 0 variables in file "%s" that are not wrapped in ${}.', $valid_file) . PHP_EOL, implode(PHP_EOL, $output));

    $output = $this->runExecute($command, ['path' => $valid_file, '-f' => TRUE]);
    $this->assertEquals(0, $this->commandTester->getStatusCode());
    $this->assertEquals(sprintf('Replaced 0 variables in file "%s".', $valid_file) . PHP_EOL, implode(PHP_EOL, $output));
    $this->assertFileEquals($valid_file, $valid_file_not_run);

    // Invalid file.
    $invalid_file = $this->createTempFileFromFixtureFile('unwrapped.sh');
    $invalid_file_initial = $this->createTempFileFromFixtureFile('unwrapped.sh', 'initial');
    $this->assertFileEquals($invalid_file, $invalid_file_initial);

    $output = $this->runExecute($command, ['path' => $invalid_file]);
    $this->assertEquals([
      '11: var=$VAR1',
      '12: var="$VAR2"',
      '14: var=$VAR3',
      sprintf('Found 3 variables in file "%s" that are not wrapped in ${}.', $invalid_file),
      '',
    ], $output);
    $this->assertEquals(1, $this->commandTester->getStatusCode());

    $output = $this->runExecute($command, ['path' => $invalid_file, '-f' => TRUE]);
    $this->assertEquals([
      'Replaced in line 11: var=$VAR1',
      'Replaced in line 12: var="$VAR2"',
      'Replaced in line 14: var=$VAR3',
      sprintf('Replaced 3 variables in file "%s".', $invalid_file),
      '',
    ], $output);
    $this->assertFileNotEquals($invalid_file, $invalid_file_initial);
    $this->assertEquals(0, $this->commandTester->getStatusCode());
  }

  /**
   * Test LintCommand.
   *
   * @throws \Exception
   */
  public function testLintCommandDir(): void {
    $command = new LintCommand();

    $valid_file = $this->createTempFileFromFixtureFile('wrapped.sh', 'dir1');
    $invalid_file = $this->createTempFileFromFixtureFile('unwrapped.sh', 'dir1');
    $invalid_file_bats = $this->createTempFileFromFixtureFile('unwrapped.bats', 'dir1');
    $invalid_file_subdir = $this->createTempFileFromFixtureFile('unwrapped.sh', 'dir1/subdir');
    $invalid_file_initial = $this->createTempFileFromFixtureFile('unwrapped.sh', 'initial');
    $file_md = $this->createTempFileFromFixtureFile('test-template-path.md', 'dir1');
    $dir = dirname($valid_file);

    // Lint.
    $output = $this->runExecute($command, ['path' => $dir]);
    $this->assertEquals(1, $this->commandTester->getStatusCode());
    $this->assertEquals([
      '11: var=$VAR1',
      '12: var="$VAR2"',
      '14: var=$VAR3',
      sprintf('Found 3 variables in file "%s" that are not wrapped in ${}.', $invalid_file_subdir),
      '11: var=$VAR1',
      '12: var="$VAR2"',
      '14: var=$VAR3',
      sprintf('Found 3 variables in file "%s" that are not wrapped in ${}.', $invalid_file),
      sprintf('Found 0 variables in file "%s" that are not wrapped in ${}.', $valid_file),
      '',
    ], $output);

    // Lint with extensions.
    $output = $this->runExecute($command, ['path' => $dir, '-e' => ['bats']]);
    $this->assertEquals(1, $this->commandTester->getStatusCode());
    $this->assertEquals([
      '11: var=$VAR1',
      '12: var="$VAR2"',
      '14: var=$VAR3',
      sprintf('Found 3 variables in file "%s" that are not wrapped in ${}.', $invalid_file_bats),
      '',
    ], $output);

    // Lint fix.
    $output = $this->runExecute($command, ['path' => $dir, '-f' => TRUE]);
    $this->assertEquals(0, $this->commandTester->getStatusCode());
    $this->assertEquals([
      'Replaced in line 11: var=$VAR1',
      'Replaced in line 12: var="$VAR2"',
      'Replaced in line 14: var=$VAR3',
      sprintf('Replaced 3 variables in file "%s".', $invalid_file_subdir),
      'Replaced in line 11: var=$VAR1',
      'Replaced in line 12: var="$VAR2"',
      'Replaced in line 14: var=$VAR3',
      sprintf('Replaced 3 variables in file "%s".', $invalid_file),
      sprintf('Replaced 0 variables in file "%s".', $valid_file),
      '',
    ], $output);
    $this->assertFileNotEquals($invalid_file, $invalid_file_initial);

    // Empty dir.
    unlink($valid_file);
    unlink($invalid_file);
    unlink($invalid_file_subdir);
    unlink($file_md);
    unlink($invalid_file_bats);
    $output = $this->runExecute($command, ['path' => $dir]);
    $this->assertEquals(0, $this->commandTester->getStatusCode());
    $this->assertEquals([
      '',
    ], $output);
  }

}

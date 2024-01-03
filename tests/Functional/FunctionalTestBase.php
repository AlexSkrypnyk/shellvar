<?php

declare(strict_types = 1);

namespace AlexSkrypnyk\ShellVariablesExtractor\Tests\Functional;

use AlexSkrypnyk\ShellVariablesExtractor\Command\VariablesExtractorCommand;
use AlexSkrypnyk\ShellVariablesExtractor\Tests\Unit\UnitTestBase;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class FunctionalTestBase.
 *
 * Base class for functional tests.
 */
abstract class FunctionalTestBase extends UnitTestBase {

  /**
   * Command Tester.
   *
   * @var \Symfony\Component\Console\Tester\CommandTester
   */
  protected CommandTester $commandTester;

  protected function setUp(): void {
    parent::setUp();

    $singleCommandApplication = new SingleCommandApplication();
    $singleCommandApplication->setAutoExit(FALSE);
    $singleCommandApplication->setCode([new VariablesExtractorCommand($singleCommandApplication), 'execute']);
    $this->commandTester = new CommandTester($singleCommandApplication);
  }

  /**
   * Execute command.
   *
   * @param array<mixed> $input
   *   Command input.
   * @param array<mixed> $options
   *   Command options.
   *
   * @return array{'code': int, 'output': string}
   *   The code and output.
   */
  protected function runExecute(array $input, array $options = []): array {
    $code = $this->commandTester->execute($input, $options);
    $outputDisplay = $this->commandTester->getDisplay();

    return [
      'code' => $code,
      'output' => $outputDisplay,
    ];
  }

}

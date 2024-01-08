<?php

declare(strict_types = 1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\Command\ShellvarCommand;
use AlexSkrypnyk\Shellvar\Tests\Unit\UnitTestBase;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class FunctionalTestBase.
 *
 * Base class for functional tests.
 */
abstract class FunctionalTestBase extends UnitTestBase {

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
    $singleCommandApplication = new SingleCommandApplication();
    $singleCommandApplication->setAutoExit(FALSE);
    $singleCommandApplication->setCode([new ShellvarCommand($singleCommandApplication), 'execute']);
    $commandTester = new CommandTester($singleCommandApplication);
    $code = $commandTester->execute($input, $options);
    $outputDisplay = $commandTester->getDisplay();

    return [
      'code' => $code,
      'output' => $outputDisplay,
    ];
  }

}

<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Fixtures\ConsoleAware;

use DrevOps\Shellvar\ConsoleAwareInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DummyConsoleAware1.
 *
 * Dummy console-aware class with 1 argument and 1 option.
 */
class DummyConsoleAware1 implements ConsoleAwareInterface {

  /**
   * {@inheritdoc}
   */
  public static function getConsoleArguments(): array {
    return [
      new InputArgument('argument11', InputArgument::OPTIONAL),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [
      new InputOption('option11', NULL, InputOption::VALUE_NONE),
    ];
  }

}

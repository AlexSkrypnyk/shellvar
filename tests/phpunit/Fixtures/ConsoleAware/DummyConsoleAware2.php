<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Fixtures\ConsoleAware;

use DrevOps\Shellvar\ConsoleAwareInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class DummyConsoleAware2.
 *
 * Dummy console-aware class with 2 arguments and 2 options.
 */
class DummyConsoleAware2 implements ConsoleAwareInterface {

  /**
   * {@inheritdoc}
   */
  public static function getConsoleArguments(): array {
    return [
      new InputArgument('argument21', InputArgument::OPTIONAL),
      new InputArgument('argument22', InputArgument::OPTIONAL),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [
      new InputOption('option21', NULL, InputOption::VALUE_NONE),
      new InputOption('option22', NULL, InputOption::VALUE_NONE),
    ];
  }

}

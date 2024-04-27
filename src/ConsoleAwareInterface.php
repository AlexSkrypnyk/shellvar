<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar;

/**
 * Interface ConsoleAwareInterface.
 *
 * Classes that want to provide input arguments and options for the console
 * should implement this interface.
 */
interface ConsoleAwareInterface {

  /**
   * Returns an array of console arguments.
   *
   * @return \Symfony\Component\Console\Input\InputArgument[]
   *   An array of console arguments.
   */
  public static function getConsoleArguments(): array;

  /**
   * Returns an array of console options.
   *
   * @return \Symfony\Component\Console\Input\InputOption[]
   *   An array of console options.
   */
  public static function getConsoleOptions(): array;

}

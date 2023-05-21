<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

/**
 * Interface FormatterInterface.
 *
 * Interface for all formatters.
 */
interface FormatterInterface {

  /**
   * Get formatter name.
   *
   * @return string
   *   Formatter name.
   */
  public static function getName(): string;

  /**
   * Format variables data.
   *
   * @return string
   *   A formatted variables data as a string.
   */
  public function format(): string;

}

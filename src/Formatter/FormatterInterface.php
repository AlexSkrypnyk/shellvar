<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

/**
 * Interface FormatterInterface.
 *
 * Interface for all formatters.
 */
interface FormatterInterface {

  /**
   * Format variables data.
   *
   * @return string
   *   A formatted variables data as a string.
   */
  public function format(array $variables): string;

}

<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Formatter;

/**
 * Interface FormatterInterface.
 *
 * Interface for all formatters.
 */
interface FormatterInterface {

  /**
   * Format variables data.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   Variables.
   *
   * @return string
   *   A formatted variables data as a string.
   */
  public function format(array $variables): string;

}

<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Filter;

use AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface;

/**
 * Interface FilterInterface.
 *
 * Interface for all filters.
 */
interface FilterInterface extends FactoryDiscoverableInterface {

  /**
   * Format variables data.
   *
   * @return string
   *   A formatted variables data as a string.
   */
  public function filter(array $variables): array;

  /**
   * Get the priority of the filter.
   *
   * Higher priority means the filter will be applied sooner.
   */
  public static function getPriority():int;

}

<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Filter;

use DrevOps\Shellvar\Factory\FactoryDiscoverableInterface;

/**
 * Interface FilterInterface.
 *
 * Interface for all filters.
 */
interface FilterInterface extends FactoryDiscoverableInterface {

  /**
   * Filter variables.
   *
   * @param array<mixed> $variables
   *   Variables to filter.
   *
   * @return array<mixed>
   *   Filtered variables.
   */
  public function filter(array $variables): array;

  /**
   * Get the priority of the filter.
   *
   * Higher priority means the filter will be applied sooner.
   */
  public static function getPriority(): int;

}

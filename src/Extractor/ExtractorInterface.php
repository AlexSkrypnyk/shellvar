<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Extractor;

use AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface;

/**
 * Interface ExtractorInterface.
 *
 * Extracts variables from the provided targets.
 */
interface ExtractorInterface extends FactoryDiscoverableInterface {

  /**
   * Extracts variables from the targets provided in the configuration.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   Array of extracted variables.
   */
  public function extract(): array;

}

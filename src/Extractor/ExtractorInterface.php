<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Extractor;

use AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface;

/**
 * Interface ExtractorInterface.
 *
 * Extracts variables from the provided targets.
 */
interface ExtractorInterface extends FactoryDiscoverableInterface {

  /**
   * Extracts variables from the targets provided in the configuration.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
   *   Array of extracted variables.
   */
  public function extract(): array;

}

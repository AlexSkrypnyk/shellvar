<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Extractor;

/**
 * Interface ExtractorInterface.
 *
 * Extracts variables from the provided targets.
 */
interface ExtractorInterface {

  /**
   * Extracts variables from the provided targets.
   *
   * @param array $targets
   *   Array of target files.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[]
   *   Array of extracted variables.
   */
  public function extract(array $targets): array;

}

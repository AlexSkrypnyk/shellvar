<?php

namespace AlexSkrypnyk\Shellvar\Extractor;

use AlexSkrypnyk\Shellvar\AbstractManager;

/**
 * Class ExtractorManager.
 *
 * Manage extractors and run the extraction.
 */
class ExtractorManager extends AbstractManager {

  /**
   * Extract variables from the targets provided in configs.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   Array of extracted variables.
   */
  public function extract() {
    // Using hardcoded 'extractor-shell' as we only have a single extractor.
    $extractor = $this->factory->create('extractor-shell', $this->config);
    // @phpstan-ignore-next-line
    return $extractor->extract();
  }

  /**
   * {@inheritdoc}
   */
  protected static function getDiscoveryDirectory(): string {
    return __DIR__ . '/../Extractor';
  }

}

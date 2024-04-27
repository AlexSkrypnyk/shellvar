<?php

declare(strict_types=1);

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
  public function extract(): array {
    // Using hardcoded 'extractor-shell' as we only have a single extractor.
    /** @var \AlexSkrypnyk\Shellvar\Extractor\ExtractorInterface $extractor */
    $extractor = $this->factory->create('extractor-shell', $this->config);

    return $extractor->extract();
  }

  /**
   * {@inheritdoc}
   */
  protected static function getDiscoveryDirectory(): string {
    return __DIR__ . '/../Extractor';
  }

}

<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Extractor;

use DrevOps\Shellvar\AbstractManager;

/**
 * Class ExtractorManager.
 *
 * Manage extractors and run the extraction.
 */
class ExtractorManager extends AbstractManager {

  /**
   * Extract variables from the targets provided in configs.
   *
   * @return \DrevOps\Shellvar\Variable\Variable[]
   *   Array of extracted variables.
   */
  public function extract(): array {
    // Using hardcoded 'extractor-shell' as we only have a single extractor.
    /** @var \DrevOps\Shellvar\Extractor\ExtractorInterface $extractor */
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

<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Filter;

use DrevOps\Shellvar\AbstractManager;
use DrevOps\Shellvar\Config\Config;

/**
 * Class FilterManager.
 *
 * Manage filters and apply filtering.
 */
class FilterManager extends AbstractManager {

  /**
   * A list of discovered filters.
   *
   * @var \DrevOps\Shellvar\Filter\FilterInterface[]
   */
  protected array $filters;

  /**
   * FilterManager constructor.
   *
   * @param \DrevOps\Shellvar\Config\Config $config
   *   The configuration.
   */
  public function __construct(Config $config) {
    parent::__construct($config);

    /** @var \DrevOps\Shellvar\Filter\FilterInterface[] $filters */
    $filters = $this->factory->createAll($this->getConfig());

    $this->filters = $filters;

    usort($this->filters, static fn(FilterInterface $a, FilterInterface $b): int => $b::getPriority() <=> $a::getPriority());
  }

  /**
   * Filters variables.
   *
   * @param array<mixed> $variables
   *   Variables list.
   *
   * @return array<mixed>
   *   Filter variables using discovered filters.
   */
  public function filter(array $variables): array {
    foreach ($this->filters as $filter) {
      $variables = $filter->filter($variables);
    }

    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getDiscoveryDirectory(): string {
    return __DIR__ . '/../Filter';
  }

}

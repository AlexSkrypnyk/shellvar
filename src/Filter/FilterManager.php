<?php

namespace AlexSkrypnyk\Shellvar\Filter;

use AlexSkrypnyk\Shellvar\AbstractManager;
use AlexSkrypnyk\Shellvar\Config\Config;

/**
 * Class FilterManager.
 *
 * Manage filters and apply filtering.
 */
class FilterManager extends AbstractManager {

  /**
   * A list of discovered filters.
   *
   * @var \AlexSkrypnyk\Shellvar\Filter\FilterInterface[]
   */
  protected $filters = [];

  /**
   * FilterManager constructor.
   *
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   The configuration.
   */
  public function __construct(Config $config) {
    parent::__construct($config);
    $this->filters = $this->factory->createAll($this->getConfig());
    usort($this->filters, function (FilterInterface $a, FilterInterface $b) {
      return $b::getPriority() <=> $a::getPriority();
    });
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
  public function filter(array $variables) : array {
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

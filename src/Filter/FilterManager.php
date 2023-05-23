<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Filter;

use AlexSkrypnyk\ShellVariablesExtractor\AbstractManager;
use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;

/**
 * Class FilterManager.
 *
 * Manage filters and apply filtering.
 */
class FilterManager extends AbstractManager {

  /**
   * A list of discovered filters.
   *
   * @var \AlexSkrypnyk\ShellVariablesExtractor\Filter\FilterInterface[]
   */
  protected $filters = [];

  /**
   * FilterManager constructor.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
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
   * Filter variables using discovered filters.
   */
  public function filter(array $variables) {
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

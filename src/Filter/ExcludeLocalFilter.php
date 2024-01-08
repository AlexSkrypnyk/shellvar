<?php

namespace AlexSkrypnyk\Shellvar\Filter;

use AlexSkrypnyk\Shellvar\Variable\Variable;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExcludeLocalFilter.
 *
 * Filter out local variables.
 */
class ExcludeLocalFilter extends AbstractFilter {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'filter-exclude-local';
  }

  /**
   * {@inheritdoc}
   */
  public static function getPriority():int {
    return 100;
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [
      new InputOption(
        name: 'exclude-local',
        mode: InputOption::VALUE_NONE,
        description: 'Remove local variables.'
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function filter(array $variables): array {
    // @phpstan-ignore-next-line
    return array_filter($variables, function (Variable $variable) {
      return $variable->getName() != strtolower($variable->getName());
    });
  }

}

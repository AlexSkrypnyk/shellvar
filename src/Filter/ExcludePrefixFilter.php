<?php

namespace AlexSkrypnyk\Shellvar\Filter;

use AlexSkrypnyk\Shellvar\Variable\Variable;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ExcludePrefixFilter.
 *
 * Filter out excluded prefixed variables.
 */
class ExcludePrefixFilter extends AbstractFilter {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'filter-exclude-prefix';
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [
      new InputOption(
        name: 'exclude-prefix',
        mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        description: 'Exclude variables that start with the provided prefix.'
      ),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function filter($variables): array {
    $prefixes = $this->config->get('exclude-prefix');
    // @phpstan-ignore-next-line
    return array_filter($variables, static function (Variable $variable) use ($prefixes) : bool {
      // @phpstan-ignore-next-line
      return !array_filter($prefixes, static fn($p): bool => str_starts_with($variable->getName(), $p));
    });
  }

}

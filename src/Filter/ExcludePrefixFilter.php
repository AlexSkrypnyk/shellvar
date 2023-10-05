<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Filter;

use AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable;
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
    return array_filter($variables, function (Variable $variable) use ($prefixes) {
      // @phpstan-ignore-next-line
      return !array_filter($prefixes, fn($p) => str_starts_with($variable->getName(), $p));
    });
  }

}

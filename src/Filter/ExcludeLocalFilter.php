<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Filter;

use DrevOps\Shellvar\Variable\Variable;
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
  public static function getPriority(): int {
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
    $exclude_local = (bool) $this->config->get('exclude-local');

    return array_filter($variables, static function ($variable) use ($exclude_local): bool {
      if (!$variable instanceof Variable) {
        return FALSE;
      }

      return !$exclude_local || $variable->getName() !== strtolower($variable->getName());
    });
  }

}

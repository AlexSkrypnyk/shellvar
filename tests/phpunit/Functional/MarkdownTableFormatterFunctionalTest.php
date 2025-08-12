<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\AbstractManager;
use AlexSkrypnyk\Shellvar\Extractor\AbstractExtractor;
use AlexSkrypnyk\Shellvar\Extractor\ExtractorManager;
use AlexSkrypnyk\Shellvar\Extractor\ShellExtractor;
use AlexSkrypnyk\Shellvar\Filter\AbstractFilter;
use AlexSkrypnyk\Shellvar\Filter\ExcludeFromFileFilter;
use AlexSkrypnyk\Shellvar\Filter\ExcludeLocalFilter;
use AlexSkrypnyk\Shellvar\Filter\ExcludePrefixFilter;
use AlexSkrypnyk\Shellvar\Filter\FilterManager;
use AlexSkrypnyk\Shellvar\Formatter\AbstractFormatter;
use AlexSkrypnyk\Shellvar\Formatter\FormatterManager;
use AlexSkrypnyk\Shellvar\Formatter\MarkdownTableFormatter;
use AlexSkrypnyk\Shellvar\Formatter\AbstractMarkdownFormatter;
use AlexSkrypnyk\Shellvar\Command\ExtractCommand;
use AlexSkrypnyk\Shellvar\Utils;
use AlexSkrypnyk\Shellvar\Variable\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Class MarkdownTableFormatterFunctionalTest.
 *
 * Functional tests for extractions.
 */
#[CoversClass(AbstractExtractor::class)]
#[CoversClass(AbstractFilter::class)]
#[CoversClass(AbstractFormatter::class)]
#[CoversClass(AbstractManager::class)]
#[CoversClass(AbstractMarkdownFormatter::class)]
#[CoversClass(ExcludeFromFileFilter::class)]
#[CoversClass(ExcludeLocalFilter::class)]
#[CoversClass(ExcludePrefixFilter::class)]
#[CoversClass(ExtractCommand::class)]
#[CoversClass(ExtractorManager::class)]
#[CoversClass(FilterManager::class)]
#[CoversClass(FormatterManager::class)]
#[CoversClass(MarkdownTableFormatter::class)]
#[CoversClass(ShellExtractor::class)]
#[CoversClass(Utils::class)]
#[CoversClass(Variable::class)]
#[Group('scripts')]
class MarkdownTableFormatterFunctionalTest extends FormatterFunctionalTestCase {

  public static function dataProviderFormatter(): array {
    return [
      [
        'No inline code wraps',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--md-no-inline-code-wrap-vars' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Custom fields',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--fields' => 'name=Name;path=Path',
          '--md-no-inline-code-wrap-vars' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          // @phpstan-ignore-next-line
          '--path-strip-prefix' => dirname(realpath(__DIR__ . '/..')),
          'paths' => [
            self::fixtureFile('test-data.bash'),
            self::fixtureFile('test-data.sh'),
          ],
        ],
      ],

      [
        'Wrapped in inline code',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Wrapped in inline code with numbers',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Wrapped in inline code with extras',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          '--md-inline-code-extra-file' => [self::fixtureFile('test-data-ticks-included.txt')],
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],
    ];
  }

}

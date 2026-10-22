<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Functional;

use DrevOps\Shellvar\AbstractManager;
use DrevOps\Shellvar\Extractor\AbstractExtractor;
use DrevOps\Shellvar\Extractor\ExtractorManager;
use DrevOps\Shellvar\Extractor\ShellExtractor;
use DrevOps\Shellvar\Filter\AbstractFilter;
use DrevOps\Shellvar\Filter\ExcludeFromFileFilter;
use DrevOps\Shellvar\Filter\ExcludeLocalFilter;
use DrevOps\Shellvar\Filter\ExcludePrefixFilter;
use DrevOps\Shellvar\Filter\FilterManager;
use DrevOps\Shellvar\Formatter\AbstractFormatter;
use DrevOps\Shellvar\Formatter\FormatterManager;
use DrevOps\Shellvar\Formatter\MarkdownTableFormatter;
use DrevOps\Shellvar\Formatter\AbstractMarkdownFormatter;
use DrevOps\Shellvar\Command\ExtractCommand;
use DrevOps\Shellvar\Utils;
use DrevOps\Shellvar\Variable\Variable;
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

      [
        'Column order',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--column-order' => 'Description,Name',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Only columns',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--only-columns' => 'Name,Description',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Exclude columns',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--exclude-columns' => 'Default value',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Combined column options',
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--only-columns' => 'Name,Description,Default value',
          '--column-order' => 'Description,Name',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Links',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-table',
          '--md-link-vars' => TRUE,
          '--md-link-vars-anchor-case' => 'lower',
          'paths' => [self::fixtureFile('test-data-links.sh')],
        ],
      ],

      [
        'Links - upper case',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-table',
          '--md-link-vars' => TRUE,
          '--md-link-vars-anchor-case' => 'upper',
          'paths' => [self::fixtureFile('test-data-links.sh')],
        ],
      ],

      [
        'Links - preserve case',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-table',
          '--md-link-vars' => TRUE,
          '--md-link-vars-anchor-case' => 'preserve',
          'paths' => [self::fixtureFile('test-data-links.sh')],
        ],
      ],
    ];
  }

}

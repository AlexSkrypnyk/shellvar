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
use AlexSkrypnyk\Shellvar\Formatter\MarkdownBlocksFormatter;
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
#[CoversClass(MarkdownBlocksFormatter::class)]
#[CoversClass(ShellExtractor::class)]
#[CoversClass(Utils::class)]
#[CoversClass(Variable::class)]
#[Group('scripts')]
class MarkdownBlocksFormatterFunctionalTest extends FormatterFunctionalTestCase {

  public static function dataProviderFormatter(): array {
    return [
      [
        'Not wrapped in inline code',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-vars' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Wrapped in inline code',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Wrapped in inline code with numbers',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Wrapped in inline code with extras',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-numbers' => TRUE,
          '--md-inline-code-extra-file' => [self::fixtureFile('test-data-ticks-included.txt')],
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Wrapped in inline code with path',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-numbers' => TRUE,
          '--md-inline-code-extra-file' => [self::fixtureFile('test-data-ticks-included.txt')],
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Template',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-block-template-file' => self::fixtureFile('test-template-path.md'),
          // @phpstan-ignore-next-line
          '--path-strip-prefix' => dirname(realpath(__DIR__ . '/..')),
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [self::fixtureDir() . '/multipath'],
        ],
      ],

      [
        'List',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [self::fixtureFile('test-data-list.sh')],
        ],
      ],

      [
        'Links',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-link-vars' => TRUE,
          '--md-link-vars-anchor-case' => 'lower',
          'paths' => [self::fixtureFile('test-data-links.sh')],
        ],
      ],

      [
        'Skip - default',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          'paths' => [self::fixtureFile('test-data-skip-text.sh')],
        ],
      ],

      [
        'Skip - custom',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--skip-text' => 'docs-skip',
          'paths' => [self::fixtureFile('test-data-skip-text.sh')],
        ],
      ],
    ];
  }

}

<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

/**
 * Class MarkdownTableFormatterFunctionalTest.
 *
 * Functional tests for extractions.
 *
 * @group scripts
 *
 * @covers \AlexSkrypnyk\Shellvar\Command\ExtractCommand
 * @covers \AlexSkrypnyk\Shellvar\Formatter\AbstractMarkdownFormatter
 * @covers \AlexSkrypnyk\Shellvar\Formatter\MarkdownBlocksFormatter
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
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

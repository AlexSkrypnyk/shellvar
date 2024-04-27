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
 * @covers \AlexSkrypnyk\Shellvar\Formatter\MarkdownTableFormatter
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
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

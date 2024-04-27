<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

/**
 * Class CsvFormatterFunctionalTest.
 *
 * Functional tests for extractions.
 *
 * @group scripts
 *
 * @covers \AlexSkrypnyk\Shellvar\Command\ExtractCommand
 * @covers \AlexSkrypnyk\Shellvar\Formatter\CsvFormatter
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
class CsvFormatterFunctionalTest extends FormatterFunctionalTestCase {

  /**
   * {@inheritdoc}
   */
  public static string $extension = '.csv';

  public static function dataProviderFormatter(): array {
    return [
      [
        'Extract all variables',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Extract all variables, custom header',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--fields' => 'name=Name;path=Path',
          // @phpstan-ignore-next-line
          '--path-strip-prefix' => dirname(realpath(__DIR__ . '/..')),
          'paths' => [
            self::fixtureFile('test-data.bash'),
            self::fixtureFile('test-data.sh'),
          ],
        ],
      ],

      [
        'Filter-out variables by exclude file',
        [
          '--exclude-local' => TRUE,
          '--exclude-from-file' => [self::fixtureFile('test-data-excluded.txt')],
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Filter-out variables by prefix',
        [
          '--exclude-local' => TRUE,
          '--exclude-from-file' => [self::fixtureFile('test-data-excluded.txt')],
          '--exclude-prefix' => ['VAR1'],
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Extract all variables from a directory',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          'paths' => [self::fixtureDir() . '/dir'],
        ],
      ],

      [
        'Extract all variables from multiple files',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          'paths' => [
            self::fixtureFile('test-data.bash'),
            self::fixtureFile('test-data.sh'),
          ],
        ],
      ],
    ];
  }

}

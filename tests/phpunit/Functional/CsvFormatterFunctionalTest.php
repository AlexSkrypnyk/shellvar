<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Functional;

use DrevOps\Shellvar\AbstractManager;
use DrevOps\Shellvar\Command\ExtractCommand;
use DrevOps\Shellvar\Extractor\AbstractExtractor;
use DrevOps\Shellvar\Extractor\ExtractorManager;
use DrevOps\Shellvar\Extractor\ShellExtractor;
use DrevOps\Shellvar\Filter\AbstractFilter;
use DrevOps\Shellvar\Filter\ExcludeFromFileFilter;
use DrevOps\Shellvar\Filter\ExcludeLocalFilter;
use DrevOps\Shellvar\Filter\ExcludePrefixFilter;
use DrevOps\Shellvar\Filter\FilterManager;
use DrevOps\Shellvar\Formatter\AbstractFormatter;
use DrevOps\Shellvar\Formatter\CsvFormatter;
use DrevOps\Shellvar\Formatter\FormatterManager;
use DrevOps\Shellvar\Utils;
use DrevOps\Shellvar\Variable\Variable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Class CsvFormatterFunctionalTest.
 *
 * Functional tests for extractions.
 */
#[CoversClass(AbstractExtractor::class)]
#[CoversClass(AbstractFilter::class)]
#[CoversClass(AbstractFormatter::class)]
#[CoversClass(AbstractManager::class)]
#[CoversClass(CsvFormatter::class)]
#[CoversClass(ExcludeFromFileFilter::class)]
#[CoversClass(ExcludeLocalFilter::class)]
#[CoversClass(ExcludePrefixFilter::class)]
#[CoversClass(ExtractCommand::class)]
#[CoversClass(ExtractorManager::class)]
#[CoversClass(FilterManager::class)]
#[CoversClass(FormatterManager::class)]
#[CoversClass(ShellExtractor::class)]
#[CoversClass(Utils::class)]
#[CoversClass(Variable::class)]
#[Group('scripts')]
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

      [
        'Column order',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--column-order' => 'Description,Name',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Only columns',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--only-columns' => 'Name,Description',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Exclude columns',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--exclude-columns' => 'Default value',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Combined column options',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--only-columns' => 'Name,Description,Default value',
          '--column-order' => 'Description,Name',
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
      ],

      [
        'Local variables are kept without --exclude-local',
        [
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data-local.sh')],
        ],
      ],

      [
        'Local variables are removed with --exclude-local',
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data-local.sh')],
        ],
      ],
    ];
  }

}

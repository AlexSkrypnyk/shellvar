<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\AbstractManager;
use AlexSkrypnyk\Shellvar\Command\ExtractCommand;
use AlexSkrypnyk\Shellvar\Extractor\AbstractExtractor;
use AlexSkrypnyk\Shellvar\Extractor\ExtractorManager;
use AlexSkrypnyk\Shellvar\Extractor\ShellExtractor;
use AlexSkrypnyk\Shellvar\Filter\AbstractFilter;
use AlexSkrypnyk\Shellvar\Filter\ExcludeFromFileFilter;
use AlexSkrypnyk\Shellvar\Filter\ExcludeLocalFilter;
use AlexSkrypnyk\Shellvar\Filter\ExcludePrefixFilter;
use AlexSkrypnyk\Shellvar\Filter\FilterManager;
use AlexSkrypnyk\Shellvar\Formatter\AbstractFormatter;
use AlexSkrypnyk\Shellvar\Formatter\CsvFormatter;
use AlexSkrypnyk\Shellvar\Formatter\FormatterManager;
use AlexSkrypnyk\Shellvar\Utils;
use AlexSkrypnyk\Shellvar\Variable\Variable;
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
    ];
  }

}

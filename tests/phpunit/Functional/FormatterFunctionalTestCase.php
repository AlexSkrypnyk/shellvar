<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\Command\ExtractCommand;

/**
 * Class FormatterFunctionalTestBase.
 *
 * Functional tests for formatters.
 *
 * @group scripts
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
abstract class FormatterFunctionalTestCase extends FunctionalTestCase {

  /**
   * {@inheritdoc}
   */
  public static string $extension = '.md';

  /**
   * @dataProvider dataProviderFormatter
   * @covers       \AlexSkrypnyk\Shellvar\AbstractManager
   * @covers       \AlexSkrypnyk\Shellvar\Utils
   * @covers       \AlexSkrypnyk\Shellvar\Formatter\AbstractFormatter::doFormat
   * @covers       \AlexSkrypnyk\Shellvar\Formatter\AbstractFormatter::processDescription
   * @covers       \AlexSkrypnyk\Shellvar\Config\ConfigAwareTrait
   * @covers       \AlexSkrypnyk\Shellvar\Extractor\AbstractExtractor
   * @covers       \AlexSkrypnyk\Shellvar\Extractor\ShellExtractor
   * @covers       \AlexSkrypnyk\Shellvar\Extractor\ExtractorManager
   * @covers       \AlexSkrypnyk\Shellvar\Variable\Variable
   * @covers       \AlexSkrypnyk\Shellvar\Filter\FilterManager
   * @covers       \AlexSkrypnyk\Shellvar\Filter\ExcludePrefixFilter
   * @covers       \AlexSkrypnyk\Shellvar\Filter\ExcludeLocalFilter
   * @covers       \AlexSkrypnyk\Shellvar\Filter\ExcludeFromFileFilter
   * @covers       \AlexSkrypnyk\Shellvar\Filter\AbstractFilter
   * @covers       \AlexSkrypnyk\Shellvar\Formatter\AbstractFormatter
   * @covers       \AlexSkrypnyk\Shellvar\Formatter\FormatterManager
   * @covers       \AlexSkrypnyk\Shellvar\Traits\SingletonTrait
   * @runInSeparateProcess
   */
  public function testFormatter(string $message, array|string $input): void {
    $input = is_array($input) ? $input : [$input];
    $result = $this->runExecute(ExtractCommand::class, $input);
    $actual = implode(PHP_EOL, $result);

    if (!empty(getenv('UPDATE_FIXTURES'))) {
      $this->fixtureExpectationDataProviderFilePutContents($actual, static::$extension);
    }

    $expected_output = $this->fixtureExpectationDataProviderFileGetContents(static::$extension);
    $this->assertEquals($expected_output, $actual, $message);
  }

  abstract public static function dataProviderFormatter(): array;

}

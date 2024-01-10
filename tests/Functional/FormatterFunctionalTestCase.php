<?php

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
   * @dataProvider dataProviderFormatter
   * @covers       \AlexSkrypnyk\Shellvar\Formatter\AbstractFormatter::doFormat
   * @covers       \AlexSkrypnyk\Shellvar\Formatter\AbstractFormatter::processDescription
   * @runInSeparateProcess
   */
  public function testFormatter(array|string $input, string $expected_output): void {
    $input = is_array($input) ? $input : [$input];
    $result = $this->runExecute(ExtractCommand::class, $input);
    $this->assertEquals($expected_output . \PHP_EOL, implode(\PHP_EOL, $result));
  }

  abstract public static function dataProviderFormatter(): array;

}

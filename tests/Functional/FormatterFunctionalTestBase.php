<?php

namespace AlexSkrypnyk\Tests\Functional;

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
abstract class FormatterFunctionalTestBase extends FunctionalTestBase {

  /**
   * @dataProvider dataProviderFormatter
   * @covers       \AlexSkrypnyk\ShellVariablesExtractor\Formatter\AbstractFormatter::doFormat
   * @covers       \AlexSkrypnyk\ShellVariablesExtractor\Formatter\AbstractFormatter::processDescription
   */
  public function testFormatter(array|string $args, string $expected_output): void {
    $args = is_array($args) ? $args : [$args];
    $result = $this->runScript($args, TRUE);
    $this->assertEquals($expected_output, $result['output']);
  }

  abstract public static function dataProviderFormatter(): array;

}

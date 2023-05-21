<?php

namespace AlexSkrypnyk\Tests\Functional;

/**
 * Class FormatterFunctionalTestBase.
 *
 * Functional tests for extractions.
 *
 * @group scripts
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
abstract class FormatterFunctionalTestBase extends FunctionalTestBase {

  /**
   * @dataProvider dataProviderFormatter
   */
  public function testFormatter($args, $expected_output) {
    $args = is_array($args) ? $args : [$args];
    $result = $this->runScript($args, TRUE);
    $this->assertEquals($expected_output, $result['output']);
  }

  abstract public function dataProviderFormatter();

}

<?php

namespace AlexSkrypnyk\Tests\Unit;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Filter\ExcludePrefixFilter;
use AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable;

/**
 * Class FilterUnitTest.
 *
 * Unit tests for the Filter class.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @coversDefaultClass \AlexSkrypnyk\ShellVariablesExtractor\Filter\ExcludePrefixFilter
 */
class FilterUnitTest extends UnitTestBase {

  /**
   * Tests the filterExcludedPrefixedVars() method.
   *
   * @dataProvider dataProviderFilterExcludedPrefixedVars
   * @covers ::filter
   * @covers \AlexSkrypnyk\ShellVariablesExtractor\Filter\AbstractFilter::__construct
   */
  public function testFilterExcludedPrefixedVars(array $var_names, array $prefixes, array $expected) : void {
    $vars = [];
    foreach ($var_names as $var_name) {
      $vars[] = new Variable($var_name);
    }

    $config = (new Config())->set('exclude-prefix', $prefixes);
    // @phpstan-ignore-next-line
    $filter = new ExcludePrefixFilter($config);
    $actual = $filter->filter($vars);

    $actual_names = [];
    foreach ($actual as $item) {
      // @phpstan-ignore-next-line
      $actual_names[] = $item->getName();
    }

    $this->assertEquals($expected, $actual_names);
  }

  /**
   * Data provider for testFilterExcludedPrefixedVars().
   */
  public static function dataProviderFilterExcludedPrefixedVars() : array {
    return [
      [[], [], []],
      [['VAR1'], [], ['VAR1']],
      [['VAR1'], ['RAND'], ['VAR1']],
      [['VAR1', 'VAR2'], ['RAND'], ['VAR1', 'VAR2']],
      [['VAR1', 'VAR2', 'VAR3_VAR31'], ['VAR3'], ['VAR1', 'VAR2']],
      [['VAR1', 'VAR2', 'VAR3_VAR31'], ['VAR3', 'RAND'], ['VAR1', 'VAR2']],
      [['VAR1', 'VAR2', 'VAR3_VAR31', 'VAR4_VAR41'], ['VAR3', 'VAR4', 'RAND'], ['VAR1', 'VAR2']],
      [['VAR1', 'VAR2', 'VAR31_VAR31', 'VAR4_VAR41'], ['VAR3_', 'VAR4', 'RAND'], ['VAR1', 'VAR2', 'VAR31_VAR31']],
    ];
  }

}

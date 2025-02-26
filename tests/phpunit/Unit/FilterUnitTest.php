<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Filter\AbstractFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Filter\ExcludeLocalFilter;
use AlexSkrypnyk\Shellvar\Filter\ExcludePrefixFilter;
use AlexSkrypnyk\Shellvar\Variable\Variable;

/**
 * Class FilterUnitTest.
 *
 * Unit tests for the Filter class.
 */
#[CoversClass(ExcludePrefixFilter::class)]
#[CoversClass(AbstractFilter::class)]
#[CoversClass(ExcludeLocalFilter::class)]
class FilterUnitTest extends UnitTestBase {

  /**
   * Tests the filterExcludedPrefixedVars() method.
   */
  #[DataProvider('dataProviderFilterExcludedPrefixedVars')]
  public function testFilterExcludedPrefixedVars(array $var_names, array $prefixes, array $expected): void {
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
  public static function dataProviderFilterExcludedPrefixedVars(): array {
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

  /**
   * Tests the filterExcludedPrefixedVars() method.
   */
  public function testFilterExcludedPrefixedVarsInvalid(): void {
    $vars = ['invalid1', new Variable('VAR1'), 'invalid2'];

    $filter = new ExcludePrefixFilter(new Config());
    $actual = $filter->filter($vars);

    $actual_names = [];
    foreach ($actual as $item) {
      // @phpstan-ignore-next-line
      $actual_names[] = $item->getName();
    }

    $this->assertEquals(['VAR1'], $actual_names);
  }

  /**
   * Tests the filterExcludedPrefixedVars() method.
   */
  public function testFilterExcludedLocalVarsInvalid(): void {
    $vars = ['invalid1', new Variable('VAR1'), new Variable('var2'), 'invalid2'];

    $filter = new ExcludeLocalFilter(new Config());
    $actual = $filter->filter($vars);

    $actual_names = [];
    foreach ($actual as $item) {
      // @phpstan-ignore-next-line
      $actual_names[] = $item->getName();
    }

    $this->assertEquals(['VAR1'], $actual_names);
  }

}

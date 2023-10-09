<?php

namespace AlexSkrypnyk\Tests\Unit;

use AlexSkrypnyk\ShellVariablesExtractor\Extractor\ShellExtractor;
use AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable;

/**
 * Class ExtractorUnitTest.
 *
 * Unit tests for the Extractor class.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @coversDefaultClass \AlexSkrypnyk\ShellVariablesExtractor\Extractor\ShellExtractor
 */
class ExtractorUnitTest extends UnitTestBase {

  /**
   * Tests the extractVariable() method.
   *
   * @dataProvider dataProviderExtractVariable
   * @covers ::extractVariable
   */
  public function testExtractVariable(string $line, ?Variable $expected) : void {
    $extractor = $this->prepareMock(ShellExtractor::class);
    $actual = $this->callProtectedMethod($extractor, 'extractVariable', [$line]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testExtractVariable().
   */
  public static function dataProviderExtractVariable() : array {
    return [
      ['', NULL],
      [' ', NULL],
      ["\n", NULL],

      ['VAR1', NULL],
      ['start VAR1', NULL],
      ['VAR1 end', NULL],
      ['start VAR1 end', NULL],

      ['$VAR1', new Variable('VAR1')],
      ['start $VAR1', new Variable('VAR1')],
      ['$VAR1 end', new Variable('VAR1')],
      ['start $VAR1 end', new Variable('VAR1')],
      ['${VAR1}', new Variable('VAR1')],
      ['start ${VAR1}', new Variable('VAR1')],
      ['${VAR1} end', new Variable('VAR1')],
      ['start ${VAR1} end', new Variable('VAR1')],

      ['$var1', new Variable('var1')],
      ['start $var1', new Variable('var1')],
      ['$var1 end', new Variable('var1')],
      ['start $var1 end', new Variable('var1')],
      ['${var1}', new Variable('var1')],
      ['start ${var1}', new Variable('var1')],
      ['${var1} end', new Variable('var1')],
      ['start ${var1} end', new Variable('var1')],

      ['VAR1=123', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1="123"', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1=${VAR2}', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1=${VAR2:-}', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1=${VAR2:-123}', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1="${VAR2}"', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1="${VAR2:-}"', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1="${VAR2:-123}"', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      ['VAR1= 123', (new Variable('VAR1'))->setIsAssignment(TRUE)],
      // Still a valid expression in the context of this method.
      ['$VAR1=123', new Variable('VAR1')],
      ['${VAR1}=123', new Variable('VAR1')],
      // Invalid assignment - var cannot have spaces before equal sign.
      ['VAR1 =123', NULL],
      ['VAR1 = 123', NULL],

      // Comments.
      ['#VAR1=123', NULL],
      ['#start VAR1=123', NULL],
      [' #start VAR1=123', NULL],
      [' # start VAR1=123', NULL],

      // Inline code.
      ['`$VAR1`', (new Variable('VAR1'))->setIsInlineCode(TRUE)],
      ['`${VAR1}`', (new Variable('VAR1'))->setIsInlineCode(TRUE)],
      ['start `$VAR1` end', (new Variable('VAR1'))->setIsInlineCode(TRUE)],
      ['start `${VAR1}` end', (new Variable('VAR1'))->setIsInlineCode(TRUE)],

      // Not suitable for extraction.
      ['`VAR1`', NULL],
      ['`VAR1=123`', NULL],
      ['`VAR1=${VAR2}`', NULL],
      ['`VAR1=${VAR2:-}`', NULL],
      ['`VAR1="123"`', NULL],
      ['`VAR1="${VAR2}"`', NULL],
      ['`VAR1="${VAR2:-}"`', NULL],
      ['`start VAR1=123`', NULL],
      ['`start VAR1=123 end`', NULL],
      ['`VAR1=123 end`', NULL],
      ['start `VAR1=123`', NULL],
      ['start `VAR1=123` end', NULL],
      ['`VAR1=123` end', NULL],
      ['start `VAR1=123 end`', NULL],
      ['`start VAR1=123` end', NULL],
    ];
  }

  /**
   * Tests the extractVariableValue() method.
   *
   * @dataProvider dataProviderExtractVariableValue
   * @covers ::extractVariableValue
   */
  public function testExtractVariableValue(string $line, string|int $expected) : void {
    $extractor = $this->prepareMock(ShellExtractor::class);
    $actual = $this->callProtectedMethod($extractor, 'extractVariableValue', [$line, 'TESTUNSET']);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testExtractVariable().
   *
   * Note that we only assert for assignment expressions. Non-assignment
   * expressions would not reach this method.
   */
  public static function dataProviderExtractVariableValue() : array {
    return [
      ['VAR1=', 'TESTUNSET'],
      ['VAR1= ', 'TESTUNSET'],
      ['VAR1=   ', 'TESTUNSET'],
      ['VAR1=""', 'TESTUNSET'],
      ['VAR1= ""', 'TESTUNSET'],

      ['VAR1=" "', ' '],
      ['VAR1= " "', ' '],

      ['VAR1=123', '123'],
      ['VAR1="123"', '123'],

      ['VAR1=$VAR2', 'VAR2'],
      ['VAR1="$VAR2"', 'VAR2'],
      ['VAR1=${VAR2}', 'VAR2'],
      ['VAR1="${VAR2}"', 'VAR2'],

      ['VAR1=${VAR2:-}', 'VAR2'],
      ['VAR1="${VAR2:-}"', 'VAR2'],
      ['VAR1=${VAR2:-123}', 123],
      ['VAR1="${VAR2:-123}"', 123],

      ['VAR1=${VAR2:-$VAR3}', 'VAR3'],
      ['VAR1="${VAR2:-$VAR3}"', 'VAR3'],
      ['VAR1="${VAR2:-"$VAR3"}"', 'VAR3'],
      ['VAR1=${VAR2:-${VAR3}}', 'VAR3'],
      ['VAR1=${VAR2:-"${VAR3}"}', 'VAR3'],
      ['VAR1="${VAR2:-"${VAR3}"}"', 'VAR3'],
      ['VAR1="${VAR2:-${VAR3}}"', 'VAR3'],
      ['VAR1="${VAR2:-${VAR3:-}}"', 'VAR3'],
      ['VAR1="${VAR2:-${VAR3:-567}}"', '567'],
      ['VAR1="${VAR2:-${VAR3:-567}}"', '567'],
      ['VAR1="${VAR2:-${VAR3:-"${VAR4:-567}"}}"', '567'],

      // Still a valid expression in the context of this method.
      ['$VAR1=123', 123],
      ['${VAR1}=123', 123],

      // Script arguments.
      ['VAR1=${VAR2:-$1}', 'TESTUNSET'],
      ['VAR1=${VAR2:-${1}}', 'TESTUNSET'],
      ['VAR1=${1:-}', 'TESTUNSET'],
      ['VAR1=${1:-2}', '2'],
      ['VAR1=${1:-$2}', 'TESTUNSET'],
    ];
  }

  /**
   * Tests the extractVariableValue() method.
   *
   * @dataProvider dataProviderExtractVariableDescription
   * @covers ::extractVariableDescription
   */
  public function testExtractVariableDescription(array $lines, int $line_num, array $skip_prefix, string $expected) : void {
    $extractor = $this->prepareMock(ShellExtractor::class);
    $actual = $this->callProtectedMethod($extractor, 'extractVariableDescription', [$lines, $line_num, $skip_prefix]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testExtractVariableDescription().
   */
  public static function dataProviderExtractVariableDescription() : array {
    return [
      [[], 0, [], ''],
      [[], 10, [], ''],
      [['string'], 0, [], ''],
      [['string'], 10, [], ''],
      [['# first second', 'VAR1'], 1, [], 'first second'],
      [[' ', '# first second', 'VAR1'], 2, [], 'first second'],
      [['# zero', ' ', '# first second', 'VAR1'], 3, [], 'first second'],
      [['# zero', ' ', '# first second', '#', '# third', 'VAR1'], 5, [], "first second\n" . "\n" . 'third'],
      [['# zero', ' ', '# first second', '#', '# third', '# forth', 'VAR1'], 6, [], "first second\n" . "\n" . "third\n" . 'forth'],
      //
      // Description prefixes.
      [['# zero', ' ', '#;< first second', '# third', '# forth', 'VAR1'], 5, ['#;<'], "third\n" . 'forth'],
      [['# zero', ' ', '#;< first second', '#;> third', '# forth', 'VAR1'], 5, ['#;<', '#;>'], 'forth'],
      [['# zero', ' ', '#;< first second', '#;> third', '# forth', 'VAR1'], 5, [';<', ';>'], 'forth'],
      // Special case: removing the skipped prefix should avoid additional line.
      [['# zero', ' ', '# first second', '#', '#;> third', '# forth', 'VAR1'], 6, [';<', ';>'], "first second\n" . "\n" . 'forth'],
    ];
  }

}

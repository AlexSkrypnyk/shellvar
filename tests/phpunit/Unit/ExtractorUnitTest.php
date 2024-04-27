<?php

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Extractor\ShellExtractor;
use AlexSkrypnyk\Shellvar\Variable\Variable;

/**
 * Class ExtractorUnitTest.
 *
 * Unit tests for the Extractor class.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @coversDefaultClass \AlexSkrypnyk\Shellvar\Extractor\ShellExtractor
 */
class ExtractorUnitTest extends UnitTestBase {

  /**
   * Tests the extractVariable() method.
   *
   * @dataProvider dataProviderExtractVariable
   * @covers ::extractVariable
   */
  public function testExtractVariable(string $line, ?Variable $expected): void {
    $extractor = $this->prepareMock(ShellExtractor::class, [], FALSE);
    $actual = $this->callProtectedMethod($extractor, 'extractVariable', [$line]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testExtractVariable().
   */
  public static function dataProviderExtractVariable(): array {
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

}

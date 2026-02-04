<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use AlexSkrypnyk\Shellvar\Extractor\VariableParser;

/**
 * Class ExtractorUnitTest.
 *
 * Unit tests for the Extractor class.
 */
#[CoversClass(VariableParser::class)]
#[AllowMockObjectsWithoutExpectations]
class VariableParserUnitTest extends UnitTestBase {

  /**
   * Tests the parseDescription() method.
   */
  #[DataProvider('dataProviderParseDescription')]
  public function testParseDescription(array $lines, int $line_num, array $skip_prefix, string $expected): void {
    $actual = VariableParser::parseDescription($lines, $line_num, $skip_prefix);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testParseDescription().
   */
  public static function dataProviderParseDescription(): array {
    return [
      [[], 0, [], ''],
      [[], 10, [], ''],
      [['string'], 0, [], ''],
      [['string'], 10, [], ''],
      [['# first second', 'VAR1'], 1, [], 'first second'],
      [[' ', '# first second', 'VAR1'], 2, [], 'first second'],
      [['# zero', ' ', '# first second', 'VAR1'], 3, [], 'first second'],
      [['# zero', ' ', '# first second', '#', '# third', 'VAR1'], 5, [], "first second\n\nthird"],
      [['# zero', ' ', '# first second', '#', '# third', '# forth', 'VAR1'], 6, [], "first second\n\nthird\nforth"],
      //
      // Description prefixes.
      [['# zero', ' ', '#;< first second', '# third', '# forth', 'VAR1'], 5, ['#;<'], "third\nforth"],
      [['# zero', ' ', '#;< first second', '#;> third', '# forth', 'VAR1'], 5, ['#;<', '#;>'], 'forth'],
      [['# zero', ' ', '#;< first second', '#;> third', '# forth', 'VAR1'], 5, [';<', ';>'], 'forth'],
      // Special case: removing the skipped prefix should avoid additional line.
      [['# zero', ' ', '# first second', '#', '#;> third', '# forth', 'VAR1'], 6, [';<', ';>'], "first second\n\nforth"],
    ];
  }

  /**
   * Tests the parseValue() method.
   */
  #[DataProvider('dataProviderParseVariableValue')]
  #[Group('parse_variable_value')]
  public function testParseVariableValue(string $line, string|int $expected): void {
    $actual = VariableParser::parseValue($line, 'TESTUNSET');
    $this->assertSame($expected, $actual);
  }

  /**
   * Data provider for testExtractVariable().
   *
   * Note that we only assert for assignment expressions. Non-assignment
   * expressions would not reach this method.
   */
  public static function dataProviderParseVariableValue(): array {
    return [
      ['VAR1=', 'TESTUNSET'],
      ['VAR1= ', 'TESTUNSET'],
      ['VAR1=   ', 'TESTUNSET'],

      ['VAR1=""', ''],
      ['VAR1= ""', ''],
      ['VAR1=" "', ' '],
      ['VAR1= " "', ' '],

      // Special case when variable references itself without a default value.
      ['VAR1=${VAR1:-}', 'TESTUNSET'],

      ['VAR1=123', '123'],
      ['VAR1="123"', '123'],

      ['VAR1=$VAR2', '${VAR2}'],
      ['VAR1="$VAR2"', '${VAR2}'],
      ['VAR1=${VAR2}', '${VAR2}'],
      ['VAR1="${VAR2}"', '${VAR2}'],

      ['VAR1=${VAR2:-}', '${VAR2}'],
      ['VAR1="${VAR2:-}"', '${VAR2}'],
      ['VAR1=${VAR2:-123}', '123'],
      ['VAR1="${VAR2:-123}"', '123'],

      ['VAR1=${VAR2:-$VAR3}', '${VAR3}'],
      ['VAR1="${VAR2:-$VAR3}"', '${VAR3}'],
      ['VAR1="${VAR2:-"$VAR3"}"', '${VAR3}'],
      ['VAR1=${VAR2:-${VAR3}}', '${VAR3}'],
      ['VAR1=${VAR2:-"${VAR3}"}', '${VAR3}'],
      ['VAR1="${VAR2:-"${VAR3}"}"', '${VAR3}'],
      ['VAR1="${VAR2:-${VAR3}}"', '${VAR3}'],
      ['VAR1="${VAR2:-${VAR3:-}}"', '${VAR3}'],
      ['VAR1="${VAR2:-${VAR3:-567}}"', '567'],
      ['VAR1="${VAR2:-${VAR3:-567}}"', '567'],
      ['VAR1="${VAR2:-${VAR3:-"${VAR4:-567}"}}"', '567'],

      // Still a valid expression in the context of this method.
      ['$VAR1=123', '123'],
      ['${VAR1}=123', '123'],

      // Script arguments.
      ['VAR1=${VAR2:-$1}', 'TESTUNSET'],
      ['VAR1=${VAR2:-${1}}', 'TESTUNSET'],
      ['VAR1=${1:-}', 'TESTUNSET'],
      ['VAR1=${1:-2}', '2'],
      ['VAR1=${1:-$2}', 'TESTUNSET'],

      // Other forms.
      ['VAR1=${VAR2-val2}', 'val2'],
      ['VAR1=${VAR2-$VAR3}', '${VAR3}'],
      ['VAR1=${VAR2-"$VAR3"}', '${VAR3}'],
      ['VAR1=${VAR2-${VAR3}}', '${VAR3}'],
      ['VAR1=${VAR2-"${VAR3}"}', '${VAR3}'],
      ['VAR1=${VAR2:-val2}', 'val2'],
      ['VAR1=${VAR2-val2}', 'val2'],
      ['VAR1=${VAR2-${VAR3:-${VAR4-val4}}}', 'val4'],
      ['VAR1=${VAR2-${VAR3:-${VAR4-}}}', '${VAR4}'],

      ['VAR1=${VAR2-${VAR3:-${VAR4?val4}}}', '${VAR4}'],
      ['VAR1=${VAR2-${VAR3:-}}', '${VAR3}'],
      ['VAR1=${VAR2-"${VAR3:-}"}', '${VAR3}'],

      ['VAR1=${VAR2:=$VAR3}', '${VAR3}'],
      ['VAR1=${VAR2+=$VAR3}', '${VAR3}'],
      ['VAR1=${VAR2?Some message}', '${VAR2}'],

      // Middle of the string.
      ['VAR1=${VAR1:-./other/${VAR2}/path}', './other/${VAR2}/path'],
      ['VAR1=${VAR1:-"./other/${VAR2}/path}"', './other/${VAR2}/path'],

      ['VAR1=${VAR1:-"${VAR2}"-"${VAR3}"}', '${VAR2}-${VAR3}'],
      ['VAR1=${VAR1:-${VAR2}-${VAR3}}', '${VAR2}-${VAR3}'],
      ['VAR1=${VAR1:-${VAR2}-${VAR3-val3}}', '${VAR2}-val3'],
      ['VAR0=${VAR1:-${VAR2-${VAR3-val3}-val4}}', 'val3-val4'],

      ['VAR1="${VAR2} \"${VAR3}\""', '${VAR2} \"${VAR3}\"'],

      ['VAR1=$(pwd)', '$(pwd)'],
    ];
  }

  /**
   * Tests the parseValue() method.
   */
  #[DataProvider('dataProviderParseValueNotation')]
  public function testParseValueNotation(string $line, string $name, ?string $operator, ?string $default): void {
    $extractor = $this->prepareMock(VariableParser::class, [], FALSE);
    $actual = (array) $this->callProtectedMethod($extractor, 'parseNotation', [$line, 'TESTUNSET']);
    $this->assertSame($name, $actual['name']);
    $this->assertSame($operator, $actual['operator']);
    $this->assertSame($default, $actual['default']);
  }

  /**
   * Data provider for testParseValueNotation().
   */
  public static function dataProviderParseValueNotation(): array {
    return [
      ['', '', NULL, NULL],

      ['VAR1', 'VAR1', NULL, NULL],

      ['VAR1-', 'VAR1', '-', NULL],
      ['VAR1:-', 'VAR1', ':-', NULL],
      ['VAR1:=', 'VAR1', ':=', NULL],
      ['VAR1+=', 'VAR1', '+=', NULL],
      ['VAR1?', 'VAR1', '?', NULL],

      ['VAR1-val', 'VAR1', '-', 'val'],
      ['VAR1:-val', 'VAR1', ':-', 'val'],
      ['VAR1:=val', 'VAR1', ':=', 'val'],
      ['VAR1+=val', 'VAR1', '+=', 'val'],
      // Special case for '?'.
      ['VAR1?val', 'VAR1', '?', NULL],

      ['VAR1-"val"', 'VAR1', '-', '"val"'],
      ['VAR1:-"val"', 'VAR1', ':-', '"val"'],
      ['VAR1:="val"', 'VAR1', ':=', '"val"'],
      ['VAR1+="val"', 'VAR1', '+=', '"val"'],
      // Special case for '?'.
      ['VAR1?"val"', 'VAR1', '?', NULL],

      ['VAR1-$VAR2', 'VAR1', '-', '$VAR2'],
      ['VAR1:-$VAR2', 'VAR1', ':-', '$VAR2'],
      ['VAR1:=$VAR2', 'VAR1', ':=', '$VAR2'],
      ['VAR1+=$VAR2', 'VAR1', '+=', '$VAR2'],
      // Special case for '?'.
      ['VAR1?$VAR2', 'VAR1', '?', NULL],

      ['VAR1-"$VAR2"', 'VAR1', '-', '"$VAR2"'],
      ['VAR1:-"$VAR2"', 'VAR1', ':-', '"$VAR2"'],
      ['VAR1:="$VAR2"', 'VAR1', ':=', '"$VAR2"'],
      ['VAR1+="$VAR2"', 'VAR1', '+=', '"$VAR2"'],
      // Special case for '?'.
      ['VAR1?"$VAR2"', 'VAR1', '?', NULL],

      // Negative.
      ['VAR1=', 'VAR1=', NULL, NULL],
      ['VAR1 =', 'VAR1 =', NULL, NULL],
    ];
  }

  /**
   * Tests the validateValue() method.
   *
   * @param string $value
   *   The value to validate.
   * @param class-string<\Throwable> $expected_exception
   *   The expected exception class.
   * @param string $expected_message
   *   The expected exception message.
   */
  #[DataProvider('dataProvidervValidateValue')]
  public function testValidateValue(string $value, ?string $expected_exception, string $expected_message): void {
    if ($expected_exception) {
      $this->expectException($expected_exception);
      $this->expectExceptionMessage($expected_message);
    }
    else {
      $this->expectNotToPerformAssertions();
    }

    $extractor = $this->prepareMock(VariableParser::class, [], FALSE);
    $this->callProtectedMethod($extractor, 'validateValue', [$value]);
  }

  /**
   * Data provider for testValidateValue().
   */
  public static function dataProvidervValidateValue(): array {
    return [
      // Valid values.
      ['Hello "World"', NULL, ''],
      ['No special characters', NULL, ''],
      ['{}', NULL, ''],
      ['{ "key": "value" }', NULL, ''],

      ['"${\'"\'}"', NULL, ''],
      ['"${s//\'"\'/%22}"', NULL, ''],
      ['"${HOME}/.ssh/id_rsa_${file//\"/}"', NULL, ''],

      // Invalid values.
      ['Hello "World', \RuntimeException::class, 'Invalid number of quotes in the value: Hello "World'],
      ['"${\'"}"', \RuntimeException::class, 'Invalid number of quotes in the value: "${\'"}"'],
      ['{Hello} World}', \RuntimeException::class, 'Unbalanced braces in the value: {Hello} World}'],
    ];
  }

}

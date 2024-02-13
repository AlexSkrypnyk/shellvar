<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Command\LintCommand;
use AlexSkrypnyk\Shellvar\Tests\Traits\FixtureTrait;

/**
 * Unit test for Lint Command.
 *
 * @coversDefaultClass \AlexSkrypnyk\Shellvar\Command\LintCommand
 */
class LintUnitTest extends UnitTestBase {

  use FixtureTrait;

  /**
   * Test process file.
   *
   * @covers ::processFile
   *
   * @throws \Exception
   */
  public function testProcessFile(): void {
    // Test file no existing.
    $lintCommand = new LintCommand();
    $result = $lintCommand->processFile('no-existing-file.sh');
    $this->assertEquals(FALSE, $result['success']);
    $this->assertEquals(['Could not open file no-existing-file.sh'], $result['messages']);

    // Test invalid file.
    $invalidFile = $this->createTempFileFromFixtureFile('unwrapped.sh');
    $result = $lintCommand->processFile($invalidFile);
    $this->assertEquals(TRUE, $result['success']);
    $this->assertEquals([
      "11: var=\$VAR1",
      "12: var=\"\$VAR2\"",
      "14: var=\$VAR3",
      sprintf('Found 3 variables in file "%s" that are not wrapped in ${}.', $invalidFile),
    ], $result['messages']);
    $result = $lintCommand->processFile($invalidFile, TRUE);
    $this->assertEquals(TRUE, $result['success']);
    $this->assertEquals([
      "Replaced in line 11: var=\$VAR1",
      "Replaced in line 12: var=\"\$VAR2\"",
      "Replaced in line 14: var=\$VAR3",
      sprintf('Replaced 3 variables in file "%s".', $invalidFile),
    ], $result['messages']);

    // Test valid file.
    $validFile = $this->createTempFileFromFixtureFile('wrapped.sh');
    $result = $lintCommand->processFile($validFile);
    $this->assertEquals(TRUE, $result['success']);
    $this->assertEquals([
      sprintf('Found 0 variables in file "%s" that are not wrapped in ${}.', $validFile),
    ], $result['messages']);
    $result = $lintCommand->processFile($validFile, TRUE);
    $this->assertEquals(TRUE, $result['success']);
    $this->assertEquals([
      sprintf('Replaced 0 variables in file "%s".', $validFile),
    ], $result['messages']);
  }

  /**
   * Test process line text.
   *
   * @param string $text
   *   Text.
   * @param string $expected_text
   *   Expected text.
   *
   * @dataProvider dataProviderProcessLine
   *
   * @covers ::processLine
   */
  public function testProcessLine(string $text, string $expected_text): void {
    $lintCommand = new LintCommand();
    $this->assertEquals($expected_text, $lintCommand->processLine($text));
  }

  /**
   * Data provider.
   *
   * @return array<string[]>
   *   Data provider.
   */
  public static function dataProviderProcessLine(): array {
    return [
      ['', ''],
      ['#', '#'],
      ['# word', '# word'],
      ['# $var', '# $var'],
      ['# word $var word', '# word $var word'],
      ['# $VAR', '# $VAR'],
      ['# word $VAR word', '# word $VAR word'],
      ['# \$VAR', '# \$VAR'],
      ['# word \$VAR word', '# word \$VAR word'],

      ['$var', '${var}'],
      ['$VAR', '${VAR}'],
      ['word $var word', 'word ${var} word'],
      ['word $VAR word', 'word ${VAR} word'],

      ['\$var', '\$var'],
      ['\$VAR', '\$VAR'],
      ['word \$var word', 'word \$var word'],
      ['word \$VAR word', 'word \$VAR word'],

      ['${var}', '${var}'],
      ['${var:-}', '${var:-}'],

      ['${var:-$other}', '${var:-${other}}'],
      ['${var:-${other}}', '${var:-${other}}'],
      ['${var:-${other:-}}', '${var:-${other:-}}'],

      ['"$var"', '"${var}"'],
      ['"\$var"', '"\$var"'],
      ['\'$var\'', '\'$var\''],
      ['\'\$var\'', '\'\$var\''],

      ['${var:-"$other"}', '${var:-"${other}"}'],

      // Contains underscore.
      ['$var_longer_123', '${var_longer_123}'],
      ['$VAR_LONGER_123', '${VAR_LONGER_123}'],
      ['word $var_longer_123 word', 'word ${var_longer_123} word'],
      ['word $VAR_LONGER_123 word', 'word ${VAR_LONGER_123} word'],

      ['\$var_longer_123', '\$var_longer_123'],
      ['\$VAR_LONGER_123', '\$VAR_LONGER_123'],
      ['word \$var_longer_123 word', 'word \$var_longer_123 word'],
      ['word \$VAR_LONGER_123 word', 'word \$VAR_LONGER_123 word'],

      ['${var_longer_123}', '${var_longer_123}'],
      ['${var_longer_123:-}', '${var_longer_123:-}'],

      ['${var_longer_123:-$other}', '${var_longer_123:-${other}}'],
      ['${var_longer_123:-${other}}', '${var_longer_123:-${other}}'],
      ['${var_longer_123:-${other:-}}', '${var_longer_123:-${other:-}}'],

      ['"$var_longer_123"', '"${var_longer_123}"'],
      ['"\$var_longer_123"', '"\$var_longer_123"'],
      ['\'$var_longer_123\'', '\'$var_longer_123\''],
      ['\'\$var_longer_123\'', '\'\$var_longer_123\''],

      ['${var_longer_123:-"$other"}', '${var_longer_123:-"${other}"}'],

      // Starts with underscore.
      ['$_var_longer_123', '${_var_longer_123}'],
      ['$_VAR_LONGER_123', '${_VAR_LONGER_123}'],
      ['word $_var_longer_123 word', 'word ${_var_longer_123} word'],
      ['word $_VAR_LONGER_123 word', 'word ${_VAR_LONGER_123} word'],

      ['\$_var_longer_123', '\$_var_longer_123'],
      ['\$_VAR_LONGER_123', '\$_VAR_LONGER_123'],
      ['word \$_var_longer_123 word', 'word \$_var_longer_123 word'],
      ['word \$_VAR_LONGER_123 word', 'word \$_VAR_LONGER_123 word'],

      ['${_var_longer_123}', '${_var_longer_123}'],
      ['${_var_longer_123:-}', '${_var_longer_123:-}'],

      ['${_var_longer_123:-$other}', '${_var_longer_123:-${other}}'],
      ['${_var_longer_123:-${other}}', '${_var_longer_123:-${other}}'],
      ['${_var_longer_123:-${other:-}}', '${_var_longer_123:-${other:-}}'],

      ['"$_var_longer_123"', '"${_var_longer_123}"'],
      ['"\$_var_longer_123"', '"\$_var_longer_123"'],
      ['\'$_var_longer_123\'', '\'$_var_longer_123\''],
      ['\'\$_var_longer_123\'', '\'\$_var_longer_123\''],
      ['${_var_longer_123:-"$other"}', '${_var_longer_123:-"${other}"}'],

      // Quotes within quotes.
      ['"\'$var\'"', '"\'${var}\'"'],
      ['"word \'$var\' word"', '"word \'${var}\' word"'],
      // And with escaped.
      ['"\'\$var\'"', '"\'\$var\'"'],

      ['string with $var1 "\'$var2\'" \'$var3\'', 'string with ${var1} "\'${var2}\'" \'$var3\''],
      ['string with $var1 "\'\$var2\'" \'$var3\'', 'string with ${var1} "\'\$var2\'" \'$var3\''],

      // Arrays.
      ['${_var_longer_array[$_var_longer_key]}', '${_var_longer_array[${_var_longer_key}]}'],
      ['${_var_longer_array["$_var_longer_key"]}', '${_var_longer_array["${_var_longer_key}"]}'],
      ['"${_var_longer_array["$_var_longer_key"]}"', '"${_var_longer_array["${_var_longer_key}"]}"'],

      ['echo "  \\$config[\'stage_file_proxy.settings\'][\'origin\'] = \'http://www.resistance-star-wars.com/\';"', 'echo "  \\$config[\'stage_file_proxy.settings\'][\'origin\'] = \'http://www.resistance-star-wars.com/\';"'],
    ];
  }

  /**
   * Test is interpolation.
   *
   * @param string $line
   *   Line text.
   * @param bool $expected
   *   Expected line text.
   *
   * @dataProvider dataProviderIsInterpolation
   *
   * @covers ::isInterpolation
   */
  public function testIsInterpolation(string $line, bool $expected): void {
    $lintCommand = new LintCommand();
    $pos = strpos($line, 'var');
    $pos = $pos === FALSE ? 0 : $pos;
    $this->assertEquals($expected, $lintCommand->isInterpolation($line, $pos));
  }

  public static function dataProviderIsInterpolation(): array {
    return [
      ['', FALSE],
      ['var', TRUE],
      [' var ', TRUE],
      ['"var"', TRUE],
      [' "var" ', TRUE],
      [' " var " ', TRUE],
      ["'var'", FALSE],
      [" 'var' ", FALSE],
      [" ' var ' ", FALSE],
      ['"\'var\'"', TRUE],
      [' "\'var\'"', TRUE],
      [' "\' var\'"', TRUE],
      [' "\' var\'" ', TRUE],
      [' "\' var\' " ', TRUE],
      [' "\' var \' " ', TRUE],

      ['\'"var"\'', TRUE],
      [' \'"var"\'', TRUE],
      [' \' "var"\'', TRUE],
      [' \' " var"\'', TRUE],
      [' \' " var" \'', TRUE],
      [' \' " var" \' ', TRUE],
      [' \' " other var" \' ', TRUE],

      ['"other" \'"var"\'', TRUE],
      [' "other" \'" var"\'', TRUE],
      [' "other" \'" var "\'', TRUE],
      [' "other " \'" var "\'', TRUE],
      [' "other " \'  " var "\'', TRUE],
      ['"other \'in single\'" \'"var"\'', TRUE],
      ['"other \'  in single\' " \'"var"\'', TRUE],

      ['"other"\'\'var\'', FALSE],
      [' "other"\'\'var\'', FALSE],
      [' "other" \'\'var\'', FALSE],
      [' "other" \' \'var\'', FALSE],
      [' "other" \' \' var\'', FALSE],
      [' "other"\' \' var\'', FALSE],
      ['"other"\'\'\'var\'', FALSE],
      [' "other"\' \'\'var\'', FALSE],
      ['"other \'quoted\' \'var\' "', TRUE],
      ['"other \' quoted\' \'var\' "', TRUE],
      [' "other \' quoted\' \'var\' "', TRUE],
      [' " other \' quoted\' \'var\' "', TRUE],

      // Broken, but starts with double.
      ['\'single"other \'in single\'" \'"var"\'', TRUE],
      // Broken, but unmatched double.
      ['\'single"other \'in single\'" "\'var\'', TRUE],
      // Broken - unmatched single.
      ['\'single"other \'in single\'" \'var\'', FALSE],
    ];
  }

}

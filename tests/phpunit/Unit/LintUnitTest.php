<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use AlexSkrypnyk\Shellvar\Command\LintCommand;
use AlexSkrypnyk\Shellvar\Tests\Traits\FixtureTrait;

/**
 * Unit test for Lint Command.
 */
#[CoversClass(LintCommand::class)]
class LintUnitTest extends UnitTestBase {

  use FixtureTrait;

  /**
   * Test process file.
   *
   * @throws \Exception
   */
  public function testProcessFile(): void {
    // Test file no existing.
    $lint_command = new LintCommand();
    $result = $lint_command->processFile('no-existing-file.sh');
    $this->assertEquals(FALSE, $result['success']);
    $this->assertEquals(['Could not open file no-existing-file.sh'], $result['messages']);

    // Test invalid file.
    $invalid_file = $this->createTempFileFromFixtureFile('unwrapped.sh');
    $result = $lint_command->processFile($invalid_file);
    $this->assertEquals(FALSE, $result['success']);
    $this->assertEquals([
      "11: var=\$VAR1",
      "12: var=\"\$VAR2\"",
      "14: var=\$VAR3",
      sprintf('Found 3 variables in file "%s" that are not wrapped in ${}.', $invalid_file),
    ], $result['messages']);
    $result = $lint_command->processFile($invalid_file, TRUE);
    $this->assertEquals(TRUE, $result['success']);
    $this->assertEquals([
      "Replaced in line 11: var=\$VAR1",
      "Replaced in line 12: var=\"\$VAR2\"",
      "Replaced in line 14: var=\$VAR3",
      sprintf('Replaced 3 variables in file "%s".', $invalid_file),
    ], $result['messages']);

    // Test valid file.
    $valid_file = $this->createTempFileFromFixtureFile('wrapped.sh');
    $result = $lint_command->processFile($valid_file);
    $this->assertEquals(TRUE, $result['success']);
    $this->assertEquals([], $result['messages']);
    $result = $lint_command->processFile($valid_file, TRUE);
    $this->assertEquals(TRUE, $result['success']);
    $this->assertEquals([
      sprintf('Replaced 0 variables in file "%s".', $valid_file),
    ], $result['messages']);
  }

  /**
   * Test process line text.
   *
   * @param string $text
   *   Text.
   * @param string $expected_text
   *   Expected text.
   */
  #[DataProvider('dataProviderProcessLine')]
  public function testProcessLine(string $text, string $expected_text): void {
    $lint_command = new LintCommand();
    $this->assertEquals($expected_text, $lint_command->processLine($text));
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
   */
  #[DataProvider('dataProviderIsInterpolation')]
  public function testIsInterpolation(string $line, bool $expected): void {
    $lint_command = new LintCommand();
    $pos = strpos($line, 'var');
    $pos = $pos === FALSE ? 0 : $pos;
    $this->assertEquals($expected, $lint_command->isInterpolation($line, $pos));
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

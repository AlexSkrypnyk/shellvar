<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Formatter\AbstractMarkdownFormatter;
use AlexSkrypnyk\Shellvar\Formatter\MarkdownBlocksFormatter;
use AlexSkrypnyk\Shellvar\Variable\Variable;

/**
 * Class FormatterUnitTest.
 *
 * Unit tests for the Formatter class.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @coversDefaultClass \AlexSkrypnyk\Shellvar\Formatter\MarkdownBlocksFormatter
 */
class FormatterUnitTest extends UnitTestBase {

  /**
   * Tests the processDescription() method.
   *
   * @dataProvider dataProviderProcessDescription
   * @covers ::processDescription
   */
  public function testProcessDescription(string $string, string $expected): void {
    $formatter = new MarkdownBlocksFormatter((new Config()));
    $actual = $this->callProtectedMethod($formatter, 'processDescription', [$string]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testExtractVariable().
   */
  public static function dataProviderProcessDescription(): array {
    return [
      ['', ''],
      [' ', ''],
      ["\n", ''],
      ["\n\n", ''],

      // Lines before and after are removed.
      [
        <<<'EOD'

        string1

        EOD,
        'string1',
      ],

      // Immediate new lines are replaced with '<br />'.
      [
        <<<'EOD'
        string1
        string2
        EOD,
        <<<'EOD'
        string1<br />string2
        EOD,
      ],

      // Single empty line preserved.
      [
        <<<'EOD'
        string1

        string2
        EOD,
        <<<'EOD'
        string1

        string2
        EOD,
      ],

      // Empty line preserved.
      [
        <<<'EOD'
        string1

        string2
        EOD,
        <<<'EOD'
        string1

        string2
        EOD,
      ],

      // Multiple empty lines converted into a single empty line.
      [
        <<<'EOD'
        string1


        string2
        EOD,
        <<<'EOD'
        string1

        string2
        EOD,
      ],

      // Empty lines before and after are removed.
      [
        <<<'EOD'

        string1


        string2

        EOD,
        <<<'EOD'
        string1

        string2
        EOD,
      ],

      // List.
      [
        <<<'EOD'
        List header

        - Item
        - Item
          Line
        - Item

        Not a list line1
        Line2
        EOD,
        <<<'EOD'
        List header

        - Item
        - Item<br />Line
        - Item

        Not a list line1<br />Line2
        EOD,
      ],

      // List - no header.
      [
        <<<'EOD'
        - Item
        - Item
          Line
        - Item

        Not a list line1
        Line2
        EOD,
        <<<'EOD'
        - Item
        - Item<br />Line
        - Item

        Not a list line1<br />Line2
        EOD,
      ],

      // List - single item.
      [
        <<<'EOD'
        - Item
        EOD,
        <<<'EOD'
        - Item
        EOD,
      ],

      // List - 2 separate lists.
      [
        <<<'EOD'
        - Item

        - Item
        EOD,
        <<<'EOD'
        - Item

        - Item
        EOD,
      ],
    ];
  }

  /**
   * Tests the processInlineCodeVars() method.
   *
   * @dataProvider dataProviderProcessInlineCodeVars
   * @covers ::processInlineCodeVars
   */
  public function testProcessInlineCodeVars(array $variables, array $tokens, array $expected): void {
    $formatter = new MarkdownBlocksFormatter((new Config()));
    $actual = $this->callProtectedMethod($formatter, 'processInlineCodeVars', [$variables, $tokens]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testProcessInlineCodeVars().
   */
  public static function dataProviderProcessInlineCodeVars(): array {
    return [
      [[], [], []],
      [[], ['token1' => 'replacement1'], []],

      [
        [
          static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description', 'val1'),
        ],
        [],
        [
          static::fixtureVariable('`VAR1`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description', '`val1`'),
        ],
      ],
      [
        [
          static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description', 'val1'),
          static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh'), 'Description', 'val2'),
        ],
        [],
        [
          static::fixtureVariable('`VAR1`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description', '`val1`'),
          static::fixtureVariable('`VAR2`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description', '`val2`'),
        ],
      ],

      // Simple tokens.
      [
        [
          static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description token1 token2 string', 'val1'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          static::fixtureVariable('`VAR1`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string', '`val1`'),
        ],
      ],
      [
        [
          static::fixtureVariable('VAR1token1', static::fixtureFile('test-data.sh'), 'Description token1 token2 string', 'val1token1'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          static::fixtureVariable('`VAR1token1`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string', '`val1token1`'),
        ],
      ],
      [
        [
          static::fixtureVariable('token1', static::fixtureFile('test-data.sh'), 'Description token1 token2 string', 'token1'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          static::fixtureVariable('`token1`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string', '`token1`'),
        ],
      ],

      // Tokens and replacements.
      [
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description token1 `token2` string VAR2, `VAR2`, $VAR2, `$VAR2`, ${VAR2}, `${VAR2}`, {VAR2}, `{VAR2}`.', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh'), 'Description token1 string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          // VAR2 is a string and not a variable. For VAR2 string to be
          // considered a variable to be wrapped in inline code, it should be
          // written as $VAR2 or ${VAR2}.
          '$VAR1' => static::fixtureVariable('`VAR1`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string VAR2, `VAR2`, `$VAR2`, `$VAR2`, `${VAR2}`, `${VAR2}`, {VAR2}, `{VAR2}`.', '`val1`'),
          '$VAR2' => static::fixtureVariable('`VAR2`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description `token1` string', '`val2`'),
        ],
      ],

      // Tokens and replacements with suffixes and prefixes.
      [
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description prefixtoken1 token1 token1suffix prefixtoken1suffix `token2` string $VAR2, ${VAR2}, PREFIX_VAR2_SUFFIX, `PREFIX_VAR2_SUFFIX`, $PREFIX_VAR2_SUFFIX, `$PREFIX_VAR2_SUFFIX`, ${PREFIX_VAR2_SUFFIX}, `${PREFIX_VAR2_SUFFIX}`, {PREFIX_VAR2_SUFFIX}, `{PREFIX_VAR2_SUFFIX}`.', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh'), 'Description prefix_token1 token1 `token1` token1_suffix prefix_token1_suffix string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          '$VAR1' => static::fixtureVariable('`VAR1`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description prefixtoken1 `token1` token1suffix prefixtoken1suffix `token2` string `$VAR2`, `${VAR2}`, PREFIX_VAR2_SUFFIX, `PREFIX_VAR2_SUFFIX`, $PREFIX_VAR2_SUFFIX, `$PREFIX_VAR2_SUFFIX`, ${PREFIX_VAR2_SUFFIX}, `${PREFIX_VAR2_SUFFIX}`, {PREFIX_VAR2_SUFFIX}, `{PREFIX_VAR2_SUFFIX}`.', '`val1`'),
          '$VAR2' => static::fixtureVariable('`VAR2`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description prefix_token1 `token1` `token1` token1_suffix prefix_token1_suffix string', '`val2`'),
        ],
      ],

      // Tokens and replacements with prefixes only.
      [
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'PREFIX_VAR2, `PREFIX_VAR2`, $PREFIX_VAR2, `$PREFIX_VAR2`, ${PREFIX_VAR2}, `${PREFIX_VAR2}`, {PREFIX_VAR2}, `{PREFIX_VAR2}`', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh'), 'Description prefix_token1 token1 `token1` token1_suffix prefix_token1_suffix string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          '$VAR1' => static::fixtureVariable('`VAR1`', '`' . static::fixtureFile('test-data.sh') . '`', 'PREFIX_VAR2, `PREFIX_VAR2`, $PREFIX_VAR2, `$PREFIX_VAR2`, ${PREFIX_VAR2}, `${PREFIX_VAR2}`, {PREFIX_VAR2}, `{PREFIX_VAR2}`', '`val1`'),
          '$VAR2' => static::fixtureVariable('`VAR2`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description prefix_token1 `token1` `token1` token1_suffix prefix_token1_suffix string', '`val2`'),
        ],
      ],

      // Tokens and replacements with suffixes only.
      [
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), '$VAR2, `$VAR2`, ${VAR2}, `${VAR2}`, VAR2_SUFFIX, `VAR2_SUFFIX`, $VAR2_SUFFIX, `$VAR2_SUFFIX`, ${VAR2_SUFFIX}, `${VAR2_SUFFIX}`, {VAR2_SUFFIX}, `{VAR2_SUFFIX}`', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh'), 'Description prefix_token1 token1 `token1` token1_suffix prefix_token1_suffix string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          '$VAR1' => static::fixtureVariable('`VAR1`', '`' . static::fixtureFile('test-data.sh') . '`', '`$VAR2`, `$VAR2`, `${VAR2}`, `${VAR2}`, VAR2_SUFFIX, `VAR2_SUFFIX`, $VAR2_SUFFIX, `$VAR2_SUFFIX`, ${VAR2_SUFFIX}, `${VAR2_SUFFIX}`, {VAR2_SUFFIX}, `{VAR2_SUFFIX}`', '`val1`'),
          '$VAR2' => static::fixtureVariable('`VAR2`', '`' . static::fixtureFile('test-data.sh') . '`', 'Description prefix_token1 `token1` `token1` token1_suffix prefix_token1_suffix string', '`val2`'),
        ],
      ],
    ];
  }

  /**
   * Tests the processLinks() method.
   *
   * @dataProvider dataProviderProcessLinks
   * @covers ::processLinks
   */
  public function testProcessLinks(array $variables, string $anchor_case, array $expected): void {
    $formatter = new MarkdownBlocksFormatter((new Config()));
    $actual = $this->callProtectedMethod($formatter, 'processLinks', [$variables, $anchor_case]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testProcessLinks().
   */
  public static function dataProviderProcessLinks(): array {
    return [
      [[], AbstractMarkdownFormatter::VARIABLE_LINK_CASE_PRESERVE, []],

      [
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description $VAR2, `$VAR2`, ${VAR2}, `${VAR2}`, VAR2', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh')),
        ],
        AbstractMarkdownFormatter::VARIABLE_LINK_CASE_PRESERVE,
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description [$VAR2](#VAR2), [`$VAR2`](#VAR2), [${VAR2}](#VAR2), [`${VAR2}`](#VAR2), VAR2', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh')),
        ],
      ],

      [
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description $VAR2, `$VAR2`, $PREFIXVAR2, $PREFIX_VAR2, $VAR2SUFFIX, $VAR2_SUFFIX, $PREFIXVAR2SUFFIX, $PREFIX_VAR2_SUFFIX', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh')),
        ],
        AbstractMarkdownFormatter::VARIABLE_LINK_CASE_PRESERVE,
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description [$VAR2](#VAR2), [`$VAR2`](#VAR2), $PREFIXVAR2, $PREFIX_VAR2, $VAR2SUFFIX, $VAR2_SUFFIX, $PREFIXVAR2SUFFIX, $PREFIX_VAR2_SUFFIX', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh')),
        ],
      ],

      [
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description $VAR2, `$VAR2`, $PREFIXVAR2, $PREFIX_VAR2, $VAR2SUFFIX, $VAR2_SUFFIX, $PREFIXVAR2SUFFIX, $PREFIX_VAR2_SUFFIX', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh')),
        ],
        AbstractMarkdownFormatter::VARIABLE_LINK_CASE_LOWER,
        [
          '$VAR1' => static::fixtureVariable('VAR1', static::fixtureFile('test-data.sh'), 'Description [$VAR2](#var2), [`$VAR2`](#var2), $PREFIXVAR2, $PREFIX_VAR2, $VAR2SUFFIX, $VAR2_SUFFIX, $PREFIXVAR2SUFFIX, $PREFIX_VAR2_SUFFIX', 'val1'),
          '$VAR2' => static::fixtureVariable('VAR2', static::fixtureFile('test-data.sh')),
        ],
      ],

    ];
  }

  /**
   * Fixture variable.
   */
  protected static function fixtureVariable(string $name, string $path, string $description = '', string $default = ''): Variable {
    $var = new Variable($name);
    $var->addPath($path);
    $var->setDescription($description);
    $var->setDefaultValue($default);

    return $var;
  }

}

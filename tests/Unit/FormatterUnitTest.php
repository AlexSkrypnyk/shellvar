<?php

namespace AlexSkrypnyk\Tests\Unit;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\MarkdownBlocksFormatter;
use AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable;

/**
 * Class FormatterUnitTest.
 *
 * Unit tests for the Formatter class.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 */
class FormatterUnitTest extends UnitTestBase {

  /**
   * Tests the processDescription() method.
   *
   * @dataProvider dataProviderProcessDescription
   */
  public function testProcessDescription($string, $expected) {
    $formatter = new MarkdownBlocksFormatter((new Config()));
    $actual = $this->callProtectedMethod($formatter, 'processDescription', [$string]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testExtractVariable().
   */
  public function dataProviderProcessDescription() {
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
   */
  public function testProcessInlineCodeVars($variables, $tokens, $expected) {
    $formatter = new MarkdownBlocksFormatter((new Config()));
    $actual = $this->callProtectedMethod($formatter, 'processInlineCodeVars', [$variables, $tokens]);
    $this->assertEquals($expected, $actual);
  }

  public function dataProviderProcessInlineCodeVars() {
    return [
      [[], [], []],
      [[], ['token1' => 'replacement1'], []],

      [
        [
          $this->fixtureVariable('VAR1', $this->fixtureFile('test-data.sh'), 'Description', 'val1'),
        ],
        [],
        [
          $this->fixtureVariable('`VAR1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description', '`val1`'),
        ],
      ],
      [
        [
          $this->fixtureVariable('VAR1', $this->fixtureFile('test-data.sh'), 'Description', 'val1'),
          $this->fixtureVariable('VAR2', $this->fixtureFile('test-data.sh'), 'Description', 'val2'),
        ],
        [],
        [
          $this->fixtureVariable('`VAR1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description', '`val1`'),
          $this->fixtureVariable('`VAR2`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description', '`val2`'),
        ],
      ],

      // Simple tokens.
      [
        [
          $this->fixtureVariable('VAR1', $this->fixtureFile('test-data.sh'), 'Description token1 token2 string', 'val1'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          $this->fixtureVariable('`VAR1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string', '`val1`'),
        ],
      ],
      [
        [
          $this->fixtureVariable('VAR1token1', $this->fixtureFile('test-data.sh'), 'Description token1 token2 string', 'val1token1'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          $this->fixtureVariable('`VAR1token1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string', '`val1token1`'),
        ],
      ],
      [
        [
          $this->fixtureVariable('token1', $this->fixtureFile('test-data.sh'), 'Description token1 token2 string', 'token1'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          $this->fixtureVariable('`token1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string', '`token1`'),
        ],
      ],

      // Tokens and replacements.
      [
        [
          '$VAR1' => $this->fixtureVariable('VAR1', $this->fixtureFile('test-data.sh'), 'Description token1 `token2` string VAR2, `VAR2`, $VAR2, `$VAR2`, ${VAR2}, `${VAR2}`, {VAR2}, `{VAR2}`.', 'val1'),
          '$VAR2' => $this->fixtureVariable('VAR2', $this->fixtureFile('test-data.sh'), 'Description token1 string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          // VAR2 is a string and not a variable. For VAR2 string to be
          // considered a variable to be wrapped in inline code, it should be
          // written as $VAR2 or ${VAR2}.
          '$VAR1' => $this->fixtureVariable('`VAR1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description `token1` `token2` string VAR2, `VAR2`, `$VAR2`, `$VAR2`, `${VAR2}`, `${VAR2}`, {VAR2}, `{VAR2}`.', '`val1`'),
          '$VAR2' => $this->fixtureVariable('`VAR2`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description `token1` string', '`val2`'),
        ],
      ],

      // Tokens and replacements with suffixes and prefixes.
      [
        [
          '$VAR1' => $this->fixtureVariable('VAR1', $this->fixtureFile('test-data.sh'), 'Description prefixtoken1 token1 token1suffix prefixtoken1suffix `token2` string $VAR2, ${VAR2}, PREFIX_VAR2_SUFFIX, `PREFIX_VAR2_SUFFIX`, $PREFIX_VAR2_SUFFIX, `$PREFIX_VAR2_SUFFIX`, ${PREFIX_VAR2_SUFFIX}, `${PREFIX_VAR2_SUFFIX}`, {PREFIX_VAR2_SUFFIX}, `{PREFIX_VAR2_SUFFIX}`.', 'val1'),
          '$VAR2' => $this->fixtureVariable('VAR2', $this->fixtureFile('test-data.sh'), 'Description prefix_token1 token1 `token1` token1_suffix prefix_token1_suffix string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          '$VAR1' => $this->fixtureVariable('`VAR1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description prefixtoken1 `token1` token1suffix prefixtoken1suffix `token2` string `$VAR2`, `${VAR2}`, PREFIX_VAR2_SUFFIX, `PREFIX_VAR2_SUFFIX`, $PREFIX_VAR2_SUFFIX, `$PREFIX_VAR2_SUFFIX`, ${PREFIX_VAR2_SUFFIX}, `${PREFIX_VAR2_SUFFIX}`, {PREFIX_VAR2_SUFFIX}, `{PREFIX_VAR2_SUFFIX}`.', '`val1`'),
          '$VAR2' => $this->fixtureVariable('`VAR2`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description prefix_token1 `token1` `token1` token1_suffix prefix_token1_suffix string', '`val2`'),
        ],
      ],

      // Tokens and replacements with prefixes only.
      [
        [
          '$VAR1' => $this->fixtureVariable('VAR1', $this->fixtureFile('test-data.sh'), 'PREFIX_VAR2, `PREFIX_VAR2`, $PREFIX_VAR2, `$PREFIX_VAR2`, ${PREFIX_VAR2}, `${PREFIX_VAR2}`, {PREFIX_VAR2}, `{PREFIX_VAR2}`', 'val1'),
          '$VAR2' => $this->fixtureVariable('VAR2', $this->fixtureFile('test-data.sh'), 'Description prefix_token1 token1 `token1` token1_suffix prefix_token1_suffix string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          '$VAR1' => $this->fixtureVariable('`VAR1`', '`' . $this->fixtureFile('test-data.sh') . '`', 'PREFIX_VAR2, `PREFIX_VAR2`, $PREFIX_VAR2, `$PREFIX_VAR2`, ${PREFIX_VAR2}, `${PREFIX_VAR2}`, {PREFIX_VAR2}, `{PREFIX_VAR2}`', '`val1`'),
          '$VAR2' => $this->fixtureVariable('`VAR2`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description prefix_token1 `token1` `token1` token1_suffix prefix_token1_suffix string', '`val2`'),
        ],
      ],

      // Tokens and replacements with suffixes only.
      [
        [
          '$VAR1' => $this->fixtureVariable('VAR1', $this->fixtureFile('test-data.sh'), '$VAR2, `$VAR2`, ${VAR2}, `${VAR2}`, VAR2_SUFFIX, `VAR2_SUFFIX`, $VAR2_SUFFIX, `$VAR2_SUFFIX`, ${VAR2_SUFFIX}, `${VAR2_SUFFIX}`, {VAR2_SUFFIX}, `{VAR2_SUFFIX}`', 'val1'),
          '$VAR2' => $this->fixtureVariable('VAR2', $this->fixtureFile('test-data.sh'), 'Description prefix_token1 token1 `token1` token1_suffix prefix_token1_suffix string', 'val2'),
        ],
        [
          'token1',
          'token2',
        ],
        [
          '$VAR1' => $this->fixtureVariable('`VAR1`', '`' . $this->fixtureFile('test-data.sh') . '`', '`$VAR2`, `$VAR2`, `${VAR2}`, `${VAR2}`, VAR2_SUFFIX, `VAR2_SUFFIX`, $VAR2_SUFFIX, `$VAR2_SUFFIX`, ${VAR2_SUFFIX}, `${VAR2_SUFFIX}`, {VAR2_SUFFIX}, `{VAR2_SUFFIX}`', '`val1`'),
          '$VAR2' => $this->fixtureVariable('`VAR2`', '`' . $this->fixtureFile('test-data.sh') . '`', 'Description prefix_token1 `token1` `token1` token1_suffix prefix_token1_suffix string', '`val2`'),
        ],
      ],
    ];
  }

  /**
   * Fixture variable.
   */
  protected function fixtureVariable($name, $path, $description = '', $default = '') {
    $var = new Variable($name);
    $var->addPath($path);
    $var->setDescription($description);
    $var->setDefaultValue($default);

    return $var;
  }

}

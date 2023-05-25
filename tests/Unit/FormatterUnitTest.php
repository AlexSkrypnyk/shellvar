<?php

namespace AlexSkrypnyk\Tests\Unit;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\MarkdownBlocksFormatter;

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

}

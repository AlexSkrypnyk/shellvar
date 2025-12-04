<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Formatter\MarkdownTableFormatter;

/**
 * Class MarkdownTableFormatterUnitTest.
 *
 * Unit tests for the MarkdownTableFormatter class.
 */
#[CoversClass(MarkdownTableFormatter::class)]
class MarkdownTableFormatterUnitTest extends UnitTestBase {

  /**
   * Tests the processDescription() method.
   *
   * This method has special logic for markdown tables:
   * - Single newlines in regular text are converted to spaces
   * - Double newlines are converted to <br/><br/>
   * - List items are preserved with <br/> between them
   * - List continuation lines get <br/> tags.
   */
  #[DataProvider('dataProviderProcessDescription')]
  public function testProcessDescription(string $string, string $expected): void {
    $formatter = new MarkdownTableFormatter((new Config()));
    $actual = $this->callProtectedMethod($formatter, 'processDescription', [$string]);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testProcessDescription().
   */
  public static function dataProviderProcessDescription(): array {
    return [
      // Empty strings.
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

      // Single newlines in regular text are converted to spaces.
      [
        <<<'EOD'
        string1
        string2
        EOD,
        'string1 string2',
      ],

      // Three lines with single newlines - all converted to spaces.
      [
        <<<'EOD'
        string1
        string2
        string3
        EOD,
        'string1 string2 string3',
      ],

      // Single empty line (double newline) converted to <br/><br/>.
      [
        <<<'EOD'
        string1

        string2
        EOD,
        <<<'EOD'
        string1<br/><br/>string2
        EOD,
      ],

      // Multiple empty lines converted into <br/><br/> (normalized by parent).
      [
        <<<'EOD'
        string1


        string2
        EOD,
        <<<'EOD'
        string1<br/><br/>string2
        EOD,
      ],

      // Empty lines before and after are removed.
      [
        <<<'EOD'

        string1


        string2

        EOD,
        <<<'EOD'
        string1<br/><br/>string2
        EOD,
      ],

      // Mixed: single newline, then double newline.
      [
        <<<'EOD'
        string1
        string2

        string3
        EOD,
        <<<'EOD'
        string1 string2<br/><br/>string3
        EOD,
      ],

      // List with header.
      [
        <<<'EOD'
        List header
        - Item1
        - Item2
        - Item3
        EOD,
        <<<'EOD'
        List header<br/>- Item1<br/>- Item2<br/>- Item3
        EOD,
      ],

      // List without header.
      [
        <<<'EOD'
        - Item1
        - Item2
        - Item3
        EOD,
        <<<'EOD'
        - Item1<br/>- Item2<br/>- Item3
        EOD,
      ],

      // List with continuation line (indentation is preserved until trim).
      [
        <<<'EOD'
        - Item1
        - Item2
          Continuation
        - Item3
        EOD,
        <<<'EOD'
        - Item1<br/>- Item2<br/>Continuation<br/>- Item3
        EOD,
      ],

      // List with multiple continuation lines (each gets <br/>).
      [
        <<<'EOD'
        - Item1
        - Item2
          Line1
          Line2
        - Item3
        EOD,
        <<<'EOD'
        - Item1<br/>- Item2<br/>Line1<br/>Line2<br/>- Item3
        EOD,
      ],

      // List followed by double newline and regular text.
      [
        <<<'EOD'
        - Item1
        - Item2

        Regular text
        EOD,
        <<<'EOD'
        - Item1<br/>- Item2<br/><br/>Regular text
        EOD,
      ],

      // List with continuation line followed by double newline.
      [
        <<<'EOD'
        - Item1
        - Item2
          Continuation

        Regular text
        EOD,
        <<<'EOD'
        - Item1<br/>- Item2<br/>Continuation<br/><br/>Regular text
        EOD,
      ],

      // List with header and double newline before list.
      [
        <<<'EOD'
        List header

        - Item1
        - Item2
        EOD,
        <<<'EOD'
        List header<br/><br/>- Item1<br/>- Item2
        EOD,
      ],

      // Complex: header, list with continuation, double newline, regular text.
      [
        <<<'EOD'
        List header
        - Item1
        - Item2
          Continuation
        - Item3

        Regular text line1
        Line2
        EOD,
        <<<'EOD'
        List header<br/>- Item1<br/>- Item2<br/>Continuation<br/>- Item3<br/><br/>Regular text line1 Line2
        EOD,
      ],

      // Multiple paragraphs of regular text.
      [
        <<<'EOD'
        Paragraph 1 line 1
        Paragraph 1 line 2

        Paragraph 2 line 1
        Paragraph 2 line 2

        Paragraph 3
        EOD,
        <<<'EOD'
        Paragraph 1 line 1 Paragraph 1 line 2<br/><br/>Paragraph 2 line 1 Paragraph 2 line 2<br/><br/>Paragraph 3
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

      // List - 2 separate lists with double newline between.
      [
        <<<'EOD'
        - Item1

        - Item2
        EOD,
        <<<'EOD'
        - Item1<br/><br/>- Item2
        EOD,
      ],

      // List with asterisks instead of hyphens.
      [
        <<<'EOD'
        * Item1
        * Item2
        EOD,
        <<<'EOD'
        * Item1<br/>* Item2
        EOD,
      ],

      // List with indented items (space before hyphen is preserved).
      [
        <<<'EOD'
         - Item1
         - Item2
        EOD,
        <<<'EOD'
        - Item1<br/> - Item2
        EOD,
      ],

      // Real-world example from test-data-list.sh.
      [
        <<<'EOD'
        Description without a leading space that goes on
        multiple lines.

        And has a comment with no content.
        EOD,
        <<<'EOD'
        Description without a leading space that goes on multiple lines.<br/><br/>And has a comment with no content.
        EOD,
      ],

      // Real-world example with list from test-data-list.sh.
      [
        <<<'EOD'
        List header.
        - "item1" - list item1.
        - "item2" - list item2.
          Second line of list item2.
        - "item3" - list item3.

        Multiple lines after a list item.
        Second line.

        And a third line.
        EOD,
        <<<'EOD'
        List header.<br/>- "item1" - list item1.<br/>- "item2" - list item2.<br/>Second line of list item2.<br/>- "item3" - list item3.<br/><br/>Multiple lines after a list item. Second line.<br/><br/>And a third line.
        EOD,
      ],
    ];
  }

}

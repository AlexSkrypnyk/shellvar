<?php

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\Tests\Unit\UnitTestBase;

/**
 * Class MarkdownTableFormatterFunctionalTest.
 *
 * Functional tests for extractions.
 *
 * @group scripts
 *
 * @covers \AlexSkrypnyk\Shellvar\Command\ShellvarCommand
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
class MarkdownBlocksFormatterFunctionalTest extends FormatterFunctionalTestBase {

  public static function dataProviderFormatter() : array {
    return [
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-vars' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [UnitTestBase::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        ### VAR1

        Default value: UNSET

        ### VAR10

        Description without a leading space.

        Default value: val10

        ### VAR11

        Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.

        Default value: val11

        ### VAR12

        Description without a leading space that goes on<br />multiple lines.
        
        And has a comment with no content.

        Default value: val12

        ### VAR13

        And has an empty line before it without a content.

        Default value: val13

        ### VAR14

        Default value: val14

        ### VAR15

        Default value: val16

        ### VAR17

        Default value: val17

        ### VAR2

        Default value: val2

        ### VAR3

        Default value: val3

        ### VAR33

        Default value: VAR32

        ### VAR34

        Default value: UNSET

        ### VAR4

        Default value: val4

        ### VAR5

        Default value: abc

        ### VAR6

        Default value: VAR5

        ### VAR7

        Default value: VAR5

        ### VAR8

        Default value: val8

        ### VAR9

        Description with leading space.

        Default value: val9

        ### VARENV1

        Default value: valenv1

        ### VARENV2

        Default value: UNSET

        ### VARENV3

        Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and testorg/test-package and `testorg/test-package` again.

        Default value: valenv3

        ### VARENV4

        Comment 2 from script without a leading space that goes on<br />multiple lines.

        Default value: UNSET

        EOD,
      ],

      // Wrapped in inline code.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [UnitTestBase::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        ### `VAR1`

        Default value: `UNSET`

        ### `VAR10`

        Description without a leading space.

        Default value: `val10`

        ### `VAR11`

        Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, `$VAR9`, VAR10 and VAR12 variable reference.

        Default value: `val11`

        ### `VAR12`

        Description without a leading space that goes on<br />multiple lines.
        
        And has a comment with no content.

        Default value: `val12`

        ### `VAR13`

        And has an empty line before it without a content.

        Default value: `val13`

        ### `VAR14`

        Default value: `val14`

        ### `VAR15`

        Default value: `val16`

        ### `VAR17`

        Default value: `val17`

        ### `VAR2`

        Default value: `val2`

        ### `VAR3`

        Default value: `val3`

        ### `VAR33`

        Default value: `VAR32`

        ### `VAR34`

        Default value: `UNSET`

        ### `VAR4`

        Default value: `val4`

        ### `VAR5`

        Default value: `abc`

        ### `VAR6`

        Default value: `VAR5`

        ### `VAR7`

        Default value: `VAR5`

        ### `VAR8`

        Default value: `val8`

        ### `VAR9`

        Description with leading space.

        Default value: `val9`

        ### `VARENV1`

        Default value: `valenv1`

        ### `VARENV2`

        Default value: `UNSET`

        ### `VARENV3`

        Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and testorg/test-package and `testorg/test-package` again.

        Default value: `valenv3`

        ### `VARENV4`

        Comment 2 from script without a leading space that goes on<br />multiple lines.

        Default value: `UNSET`

        EOD,
      ],

      // Wrapped in inline code with numbers.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          'paths' => [UnitTestBase::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        ### `VAR1`

        Default value: `UNSET`

        ### `VAR10`

        Description without a leading space.

        Default value: `val10`

        ### `VAR11`

        Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, `$VAR9`, VAR10 and VAR12 variable reference.

        Default value: `val11`

        ### `VAR12`

        Description without a leading space that goes on<br />multiple lines.
        
        And has a comment with no content.

        Default value: `val12`

        ### `VAR13`

        And has an empty line before it without a content.

        Default value: `val13`

        ### `VAR14`

        Default value: `val14`

        ### `VAR15`

        Default value: `val16`

        ### `VAR17`

        Default value: `val17`

        ### `VAR2`

        Default value: `val2`

        ### `VAR3`

        Default value: `val3`

        ### `VAR33`

        Default value: `VAR32`

        ### `VAR34`

        Default value: `UNSET`

        ### `VAR4`

        Default value: `val4`

        ### `VAR5`

        Default value: `abc`

        ### `VAR6`

        Default value: `VAR5`

        ### `VAR7`

        Default value: `VAR5`

        ### `VAR8`

        Default value: `val8`

        ### `VAR9`

        Description with leading space.

        Default value: `val9`

        ### `VARENV1`

        Default value: `valenv1`

        ### `VARENV2`

        Default value: `UNSET`

        ### `VARENV3`

        Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and testorg/test-package and `testorg/test-package` again.

        Default value: `valenv3`

        ### `VARENV4`

        Comment `2` from script without a leading space that goes on<br />multiple lines.

        Default value: `UNSET`

        EOD,
      ],

      // Wrapped in inline code with extras.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-numbers' => TRUE,
          '--md-inline-code-extra-file' => [UnitTestBase::fixtureFile('test-data-ticks-included.txt')],
          'paths' => [UnitTestBase::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        ### `VAR1`

        Default value: `UNSET`

        ### `VAR10`

        Description without a leading space.

        Default value: `val10`

        ### `VAR11`

        Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, `$VAR9`, VAR10 and VAR12 variable reference.

        Default value: `val11`

        ### `VAR12`

        Description without a leading space that goes on<br />multiple lines.
        
        And has a comment with no content.

        Default value: `val12`

        ### `VAR13`

        And has an empty line before it without a content.

        Default value: `val13`

        ### `VAR14`

        Default value: `val14`

        ### `VAR15`

        Default value: `val16`

        ### `VAR17`

        Default value: `val17`

        ### `VAR2`

        Default value: `val2`

        ### `VAR3`

        Default value: `val3`

        ### `VAR33`

        Default value: `VAR32`

        ### `VAR34`

        Default value: `UNSET`

        ### `VAR4`

        Default value: `val4`

        ### `VAR5`

        Default value: `abc`

        ### `VAR6`

        Default value: `VAR5`

        ### `VAR7`

        Default value: `VAR5`

        ### `VAR8`

        Default value: `val8`

        ### `VAR9`

        Description with leading space.

        Default value: `val9`

        ### `VARENV1`

        Default value: `valenv1`

        ### `VARENV2`

        Default value: `UNSET`

        ### `VARENV3`

        Comment from script with reference to `composer.lock` and `composer.lock` again and `somespecialtoken` and `somespecialtoken` again and `testorg/test-package` and `testorg/test-package` again.

        Default value: `valenv3`

        ### `VARENV4`

        Comment 2 from script without a leading space that goes on<br />multiple lines.

        Default value: `UNSET`

        EOD,
      ],

      // Wrapped in inline code with path.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-block-template-file' => UnitTestBase::fixtureFile('test-template-path.md'),
          // @phpstan-ignore-next-line
          '--path-strip-prefix' => dirname(realpath(__DIR__ . '/..')),
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [UnitTestBase::fixtureDir() . '/multipath'],
        ],
        <<<'EOD'
        ### `VAR11`

        Description from bash without a leading space that goes on<br />multiple lines.

        Default value: `val11bash`

        Path: `/tests/Fixtures/multipath/test-data.bash`

        Paths: `/tests/Fixtures/multipath/test-data.bash`

        ### `VAR2`

        Default value: `val2bash`

        Path: `/tests/Fixtures/multipath/test-data.bash`

        Paths: `/tests/Fixtures/multipath/test-data.bash`, `/tests/Fixtures/multipath/test-data.sh`

        EOD,
      ],

      // List.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [UnitTestBase::fixtureFile('test-data-list.sh')],
        ],
        <<<'EOD'
        ### `VAR1`

        Description without a leading space that goes on<br />multiple lines.
        
        And has a comment with no content.
        
        Default value: `val1`
        
        ### `VAR2`
        
        List header.
        - "item1" - list item1.
        - "item2" - list item2.<br />Second line of list item2.
        - "item3" - list item3.

        Multiple lines after a list item.<br />Second line.

        And a third line.
        
        Default value: `val2`

        EOD,
      ],

      // Links.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--md-link-vars' => TRUE,
          '--md-link-vars-anchor-case' => 'lower',
          'paths' => [UnitTestBase::fixtureFile('test-data-links.sh')],
        ],
        <<<'EOD'
        ### `VAR1`
        
        Reference to VAR1, `VAR1`, [`$VAR1`](#var1) and [`$VAR1`](#var1).
        
        Reference to VAR1_SUFFIX, `VAR1_SUFFIX`, $VAR1_SUFFIX and `$VAR1_SUFFIX`
        
        Reference to PREFIX_VAR1, `PREFIX_VAR1`, $PREFIX_VAR1 and `$PREFIX_VAR1`
        
        Reference to PREFIX_VAR1_SUFFIX, `PREFIX_VAR1_SUFFIX`, $PREFIX_VAR1_SUFFIX and `$PREFIX_VAR1_SUFFIX`
        
        Reference to PREFIX_VAR2_SUFFIX, `PREFIX_VAR2_SUFFIX`, $PREFIX_VAR2_SUFFIX and `$PREFIX_VAR2_SUFFIX`
        
        Default value: `UNSET`

        EOD,
      ],

      // Skip - default.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          'paths' => [UnitTestBase::fixtureFile('test-data-skip-text.sh')],
        ],
        <<<'EOD'
        ### `VAR2`

        Description from bash without a leading space that goes on<br />multiple lines.<br />@docs-skip

        Default value: `val2`

        EOD,
      ],

      // Skip - custom.
      [
        [
          '--exclude-local' => TRUE,
          '--sort' => TRUE,
          '--format' => 'md-blocks',
          '--skip-text' => 'docs-skip',
          'paths' => [UnitTestBase::fixtureFile('test-data-skip-text.sh')],
        ],
        <<<'EOD'
        ### `VAR1`

        @skip

        Default value: `val1`

        EOD,
      ],
    ];
  }

}

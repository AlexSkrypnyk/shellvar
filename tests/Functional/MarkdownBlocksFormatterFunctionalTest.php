<?php

namespace AlexSkrypnyk\Tests\Functional;

/**
 * Class MarkdownTableFormatterFunctionalTest.
 *
 * Functional tests for extractions.
 *
 * @group scripts
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
class MarkdownBlocksFormatterFunctionalTest extends FormatterFunctionalTestBase {

  public function dataProviderFormatter() {
    return [
      [
        [
          '--exclude-local',
          '--sort',
          '--format=md-blocks',
          '--md-block-template-file=' . $this->fixtureFile('test-template.md'),
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        ### VAR1

        Default value: UNSET

        ### VAR10

        Description without a leading space.

        Default value: val10

        ### VAR11

        Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.

        Default value: val11

        ### VAR12

        Description without a leading space that goes on multiple lines.<br />And has a comment with no content.

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

        Comment 2 from script without a leading space that goes on multiple lines.

        Default value: UNSET

        EOD,
      ],

      // Wrapped in inline code.
      [
        [
          '--exclude-local',
          '--sort',
          '--format=md-blocks',
          '--md-block-template-file=' . $this->fixtureFile('test-template.md'),
          '--md-inline-code-wrap-vars',
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        ### `VAR1`

        Default value: `UNSET`

        ### `VAR10`

        Description without a leading space.

        Default value: `val10`

        ### `VAR11`

        Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.

        Default value: `val11`

        ### `VAR12`

        Description without a leading space that goes on multiple lines.<br />And has a comment with no content.

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

        Comment 2 from script without a leading space that goes on multiple lines.

        Default value: `UNSET`

        EOD,
      ],

      // Wrapped in inline code with numbers.
      [
        [
          '--exclude-local',
          '--sort',
          '--format=md-blocks',
          '--md-block-template-file=' . $this->fixtureFile('test-template.md'),
          '--md-inline-code-wrap-vars',
          '--md-inline-code-wrap-numbers',
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        ### `VAR1`

        Default value: `UNSET`

        ### `VAR10`

        Description without a leading space.

        Default value: `val10`

        ### `VAR11`

        Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.

        Default value: `val11`

        ### `VAR12`

        Description without a leading space that goes on multiple lines.<br />And has a comment with no content.

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

        Comment `2` from script without a leading space that goes on multiple lines.

        Default value: `UNSET`

        EOD,
      ],

      // Wrapped in inline code with extras.
      [
        [
          '--exclude-local',
          '--sort',
          '--format=md-blocks',
          '--md-block-template-file=' . $this->fixtureFile('test-template.md'),
          '--md-inline-code-wrap-vars',
          '--md-inline-code-extra-file=' . $this->fixtureFile('test-data-ticks-included.txt'),
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        ### `VAR1`

        Default value: `UNSET`

        ### `VAR10`

        Description without a leading space.

        Default value: `val10`

        ### `VAR11`

        Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.

        Default value: `val11`

        ### `VAR12`

        Description without a leading space that goes on multiple lines.<br />And has a comment with no content.

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

        Comment 2 from script without a leading space that goes on multiple lines.

        Default value: `UNSET`

        EOD,
      ],

      // Wrapped in inline code with path.
      [
        [
          '--exclude-local',
          '--sort',
          '--format=md-blocks',
          '--md-block-template-file=' . $this->fixtureFile('test-template-path.md'),
          '--path-strip-prefix=' . dirname(realpath(__DIR__ . '/..')),
          '--md-inline-code-wrap-vars',
          $this->fixtureFile('test-data.bash'),
        ],
        <<<'EOD'
        ### `VAR11`

        Description from bash without a leading space that goes on multiple lines.
        
        Default value: `val11bash`
        
        Path: `/tests/Fixtures/test-data.bash`
        
        ### `VAR2`
        
        Default value: `val2bash`
        
        Path: `/tests/Fixtures/test-data.bash`

        EOD,
      ],

    ];
  }

}

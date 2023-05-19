<?php

namespace Drevops\Tests\Functional;

/**
 * Class HelpFunctionalTest.
 *
 * Functional tests for extractions.
 *
 * @group scripts
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
class ExtractFunctionalTest extends FunctionalTestBase {

  /**
   * @dataProvider dataProviderExtract
   */
  public function testExtract($args, $expected_output) {
    $args = is_array($args) ? $args : [$args];
    $result = $this->runScript($args, TRUE);
    $this->assertEquals($expected_output, $result['output']);
  }

  public function dataProviderExtract() {
    return [
      // Extract all variables.
      [
        [
          '--filter-global',
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        Name;"Default value";Description
        VAR1;<UNSET>;
        VAR10;val10;"Description without a leading space."
        VAR11;val11;"Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference."
        VAR12;val12;"Description without a leading space that goes on multiple lines.
        And has a comment with no content."
        VAR13;val13;"And has an empty line before it without a content."
        VAR14;val14;
        VAR15;val16;
        VAR17;val17;
        VAR2;val2;
        VAR3;val3;
        VAR33;VAR32;
        VAR4;val4;
        VAR5;abc;
        VAR6;VAR5;
        VAR7;VAR5;
        VAR8;val8;
        VAR9;val9;"Description with leading space."
        VARENV1;valenv1;
        VARENV2;<UNSET>;
        VARENV3;valenv3;"Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again."
        VARENV4;<UNSET>;"Comment 2 from script without a leading space that goes on multiple lines."
        EOD,
      ],

      // Filter-out variables by exclude file.
      [
        [
          '--filter-global',
          '--exclude-file=' . $this->fixtureFile('test-data-excluded.txt'),
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        Name;"Default value";Description
        VAR1;<UNSET>;
        VAR10;val10;"Description without a leading space."
        VAR11;val11;"Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference."
        VAR12;val12;"Description without a leading space that goes on multiple lines.
        And has a comment with no content."
        VAR13;val13;"And has an empty line before it without a content."
        VAR15;val16;
        VAR2;val2;
        VAR3;val3;
        VAR33;VAR32;
        VAR4;val4;
        VAR5;abc;
        VAR6;VAR5;
        VAR7;VAR5;
        VAR8;val8;
        VAR9;val9;"Description with leading space."
        VARENV1;valenv1;
        VARENV2;<UNSET>;
        VARENV3;valenv3;"Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again."
        VARENV4;<UNSET>;"Comment 2 from script without a leading space that goes on multiple lines."
        EOD,
      ],

      // Filter-out variables by prefix.
      [
        [
          '--filter-global',
          '--exclude-file=' . $this->fixtureFile('test-data-excluded.txt'),
          '--filter-prefix=VAR1',
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        Name;"Default value";Description
        VAR2;val2;
        VAR3;val3;
        VAR33;VAR32;
        VAR4;val4;
        VAR5;abc;
        VAR6;VAR5;
        VAR7;VAR5;
        VAR8;val8;
        VAR9;val9;"Description with leading space."
        VARENV1;valenv1;
        VARENV2;<UNSET>;
        VARENV3;valenv3;"Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again."
        VARENV4;<UNSET>;"Comment 2 from script without a leading space that goes on multiple lines."
        EOD,
      ],

      // With ticks.
      [
        [
          '--filter-global',
          '--ticks',
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        Name;"Default value";Description
        `VAR1`;`<UNSET>`;
        `VAR10`;`val10`;"Description without a leading space."
        `VAR11`;`val11`;"Description without a leading space that goes on multiple lines and has a `$VAR7`, `$VAR8`, `$VAR9`, `$VAR10` and `$VAR12` variable reference."
        `VAR12`;`val12`;"Description without a leading space that goes on multiple lines.
        And has a comment with no content."
        `VAR13`;`val13`;"And has an empty line before it without a content."
        `VAR14`;`val14`;
        `VAR15`;`val16`;
        `VAR17`;`val17`;
        `VAR2`;`val2`;
        `VAR3`;`val3`;
        `VAR33`;`VAR32`;
        `VAR4`;`val4`;
        `VAR5`;`abc`;
        `VAR6`;`VAR5`;
        `VAR7`;`VAR5`;
        `VAR8`;`val8`;
        `VAR9`;`val9`;"Description with leading space."
        `VARENV1`;`valenv1`;
        `VARENV2`;`<UNSET>`;
        `VARENV3`;`valenv3`;"Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again."
        `VARENV4`;`<UNSET>`;"Comment `2` from script without a leading space that goes on multiple lines."
        EOD,
      ],

      // With ticks with slugs.
      [
        [
          '--filter-global',
          '--ticks',
          '--slugify',
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        Name;"Default value";Description
        `VAR1`;`<UNSET>`;
        `VAR10`;`val10`;"Description without a leading space."
        `VAR11`;`val11`;"Description without a leading space that goes on multiple lines and has a [`$VAR7`](#var7), [`$VAR8`](#var8), [`$VAR9`](#var9), [`$VAR10`](#var10) and [`$VAR12`](#var12) variable reference."
        `VAR12`;`val12`;"Description without a leading space that goes on multiple lines.
        And has a comment with no content."
        `VAR13`;`val13`;"And has an empty line before it without a content."
        `VAR14`;`val14`;
        `VAR15`;`val16`;
        `VAR17`;`val17`;
        `VAR2`;`val2`;
        `VAR3`;`val3`;
        `VAR33`;`VAR32`;
        `VAR4`;`val4`;
        `VAR5`;`abc`;
        `VAR6`;`VAR5`;
        `VAR7`;`VAR5`;
        `VAR8`;`val8`;
        `VAR9`;`val9`;"Description with leading space."
        `VARENV1`;`valenv1`;
        `VARENV2`;`<UNSET>`;
        `VARENV3`;`valenv3`;"Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again."
        `VARENV4`;`<UNSET>`;"Comment `2` from script without a leading space that goes on multiple lines."
        EOD,
      ],

      // With ticks with additional ticks file.
      [
        [
          '--filter-global',
          '--ticks',
          '--slugify',
          '-l ' . $this->fixtureFile('test-data-ticks-included.txt'),
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        Name;"Default value";Description
        `VAR1`;`<UNSET>`;
        `VAR10`;`val10`;"Description without a leading space."
        `VAR11`;`val11`;"Description without a leading space that goes on multiple lines and has a [`$VAR7`](#var7), [`$VAR8`](#var8), [`$VAR9`](#var9), [`$VAR10`](#var10) and [`$VAR12`](#var12) variable reference."
        `VAR12`;`val12`;"Description without a leading space that goes on multiple lines.
        And has a comment with no content."
        `VAR13`;`val13`;"And has an empty line before it without a content."
        `VAR14`;`val14`;
        `VAR15`;`val16`;
        `VAR17`;`val17`;
        `VAR2`;`val2`;
        `VAR3`;`val3`;
        `VAR33`;`VAR32`;
        `VAR4`;`val4`;
        `VAR5`;`abc`;
        `VAR6`;`VAR5`;
        `VAR7`;`VAR5`;
        `VAR8`;`val8`;
        `VAR9`;`val9`;"Description with leading space."
        `VARENV1`;`valenv1`;
        `VARENV2`;`<UNSET>`;
        `VARENV3`;`valenv3`;"Comment from script with reference to `composer.lock` and `composer.lock` again and `somespecialtoken` and `somespecialtoken` again and `drevops/drevops-mariadb-drupal-data` and `drevops/drevops-mariadb-drupal-data` again."
        `VARENV4`;`<UNSET>`;"Comment `2` from script without a leading space that goes on multiple lines."
        EOD,
      ],

      // Extract all variables from a directory.
      [
        [
          '--filter-global',
          $this->fixtureDir(),
        ],
        <<<'EOD'
        Name;"Default value";Description
        VAR1;<UNSET>;
        VAR10;val10;"Description without a leading space."
        VAR11;val11bash;"Description from bash without a leading space that goes on multiple lines."
        VAR12;val12;"Description without a leading space that goes on multiple lines.
        And has a comment with no content."
        VAR13;val13;"And has an empty line before it without a content."
        VAR14;val14;
        VAR15;val16;
        VAR17;val17;
        VAR2;val2bash;
        VAR3;val3;
        VAR33;VAR32;
        VAR4;val4;
        VAR5;abc;
        VAR6;VAR5;
        VAR7;VAR5;
        VAR8;val8;
        VAR9;val9;"Description with leading space."
        VARENV1;valenv1_dotenv;
        VARENV2;<UNSET>;
        VARENV3;valenv3-dotenv;"Comment from script."
        VARENV4;<UNSET>;"Comment 2 from .env without a leading space that goes on multiple lines."
        EOD,
      ],

      // Extract all variables from multiple files.
      [
        [
          '--filter-global',
          $this->fixtureFile('test-data.bash'),
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        Name;"Default value";Description
        VAR1;<UNSET>;
        VAR10;val10;"Description without a leading space."
        VAR11;val11bash;"Description from bash without a leading space that goes on multiple lines."
        VAR12;val12;"Description without a leading space that goes on multiple lines.
        And has a comment with no content."
        VAR13;val13;"And has an empty line before it without a content."
        VAR14;val14;
        VAR15;val16;
        VAR17;val17;
        VAR2;val2bash;
        VAR3;val3;
        VAR33;VAR32;
        VAR4;val4;
        VAR5;abc;
        VAR6;VAR5;
        VAR7;VAR5;
        VAR8;val8;
        VAR9;val9;"Description with leading space."
        VARENV1;valenv1;
        VARENV2;<UNSET>;
        VARENV3;valenv3;"Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again."
        VARENV4;<UNSET>;"Comment 2 from script without a leading space that goes on multiple lines."
        EOD,
      ],

      // Extract all variables into markdown table.
      // Extract all variables into markdown blocks.
      [
        [
          '--filter-global',
          '--markdown=table',
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        | Name    | Default value | Description                                                                                                                                                                                                              |
        |---------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
        | VAR1    | <UNSET>       |                                                                                                                                                                                                                          |
        | VAR10   | val10         | Description without a leading space.                                                                                                                                                                                     |
        | VAR11   | val11         | Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.                                                                                    |
        | VAR12   | val12         | Description without a leading space that goes on multiple lines.<br/>And has a comment with no content.                                                                                                                  |
        | VAR13   | val13         | And has an empty line before it without a content.                                                                                                                                                                       |
        | VAR14   | val14         |                                                                                                                                                                                                                          |
        | VAR15   | val16         |                                                                                                                                                                                                                          |
        | VAR17   | val17         |                                                                                                                                                                                                                          |
        | VAR2    | val2          |                                                                                                                                                                                                                          |
        | VAR3    | val3          |                                                                                                                                                                                                                          |
        | VAR33   | VAR32         |                                                                                                                                                                                                                          |
        | VAR4    | val4          |                                                                                                                                                                                                                          |
        | VAR5    | abc           |                                                                                                                                                                                                                          |
        | VAR6    | VAR5          |                                                                                                                                                                                                                          |
        | VAR7    | VAR5          |                                                                                                                                                                                                                          |
        | VAR8    | val8          |                                                                                                                                                                                                                          |
        | VAR9    | val9          | Description with leading space.                                                                                                                                                                                          |
        | VARENV1 | valenv1       |                                                                                                                                                                                                                          |
        | VARENV2 | <UNSET>       |                                                                                                                                                                                                                          |
        | VARENV3 | valenv3       | Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again. |
        | VARENV4 | <UNSET>       | Comment 2 from script without a leading space that goes on multiple lines.                                                                                                                                               |
        EOD,
      ],

      // Extract all variables into markdown blocks.
      [
        [
          '--filter-global',
          '--markdown=' . $this->fixtureFile('test-template.md'),
          $this->fixtureFile('test-data.sh'),
        ],
        <<<'EOD'
        ### `VAR1`

        Default value: `<UNSET>`

        ### `VAR10`

        Description without a leading space.

        Default value: `val10`

        ### `VAR11`

        Description without a leading space that goes on multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.

        Default value: `val11`

        ### `VAR12`

        Description without a leading space that goes on multiple lines.<br/>And has a comment with no content.

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

        Default value: `<UNSET>`

        ### `VARENV3`

        Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and drevops/drevops-mariadb-drupal-data and `drevops/drevops-mariadb-drupal-data` again.

        Default value: `valenv3`

        ### `VARENV4`

        Comment 2 from script without a leading space that goes on multiple lines.

        Default value: `<UNSET>`

        EOD,
      ],
    ];
  }

}

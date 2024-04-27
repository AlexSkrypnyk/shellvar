<?php

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

/**
 * Class MarkdownTableFormatterFunctionalTest.
 *
 * Functional tests for extractions.
 *
 * @group scripts
 *
 * @covers \AlexSkrypnyk\Shellvar\Command\ExtractCommand
 * @covers \AlexSkrypnyk\Shellvar\Formatter\AbstractMarkdownFormatter
 * @covers \AlexSkrypnyk\Shellvar\Formatter\MarkdownTableFormatter
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
class MarkdownTableFormatterFunctionalTest extends FormatterFunctionalTestCase {

  public static function dataProviderFormatter(): array {
    return [
      [
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--md-no-inline-code-wrap-vars' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        | Name    | Default value | Description                                                                                                                                                                                |
        |---------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
        | VAR1    | UNSET         |                                                                                                                                                                                            |
        | VAR10   | val10         | Description without a leading space.                                                                                                                                                       |
        | VAR11   | val11         | Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, $VAR9, VAR10 and VAR12 variable reference.                                                 |
        | VAR12   | val12         | Description without a leading space that goes on<br />multiple lines.<br /><br />And has a comment with no content.                                                                        |
        | VAR13   | val13         | And has an empty line before it without a content.                                                                                                                                         |
        | VAR14   | val14         |                                                                                                                                                                                            |
        | VAR15   | val16         |                                                                                                                                                                                            |
        | VAR17   | val17         |                                                                                                                                                                                            |
        | VAR2    | val2          |                                                                                                                                                                                            |
        | VAR3    | val3          |                                                                                                                                                                                            |
        | VAR33   | VAR32         |                                                                                                                                                                                            |
        | VAR34   | UNSET         |                                                                                                                                                                                            |
        | VAR4    | val4          |                                                                                                                                                                                            |
        | VAR5    | abc           |                                                                                                                                                                                            |
        | VAR6    | VAR5          |                                                                                                                                                                                            |
        | VAR7    | VAR5          |                                                                                                                                                                                            |
        | VAR8    | val8          |                                                                                                                                                                                            |
        | VAR9    | val9          | Description with leading space.                                                                                                                                                            |
        | VARENV1 | valenv1       |                                                                                                                                                                                            |
        | VARENV2 | UNSET         |                                                                                                                                                                                            |
        | VARENV3 | valenv3       | Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and testorg/test-package and `testorg/test-package` again. |
        | VARENV4 | UNSET         | Comment 2 from script without a leading space that goes on<br />multiple lines.                                                                                                            |
        EOD,
      ],

      // Custom fields.
      [
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--fields' => 'name=Name;path=Path',
          '--md-no-inline-code-wrap-vars' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          // @phpstan-ignore-next-line
          '--path-strip-prefix' => dirname(realpath(__DIR__ . '/..')),
          'paths' => [
            self::fixtureFile('test-data.bash'),
            self::fixtureFile('test-data.sh'),
          ],
        ],
        <<<'EOD'
        | Name    | Path                             |
        |---------|----------------------------------|
        | VAR1    | /phpunit/Fixtures/test-data.sh   |
        | VAR10   | /phpunit/Fixtures/test-data.sh   |
        | VAR11   | /phpunit/Fixtures/test-data.bash |
        | VAR12   | /phpunit/Fixtures/test-data.sh   |
        | VAR13   | /phpunit/Fixtures/test-data.sh   |
        | VAR14   | /phpunit/Fixtures/test-data.sh   |
        | VAR15   | /phpunit/Fixtures/test-data.sh   |
        | VAR17   | /phpunit/Fixtures/test-data.sh   |
        | VAR2    | /phpunit/Fixtures/test-data.bash |
        | VAR3    | /phpunit/Fixtures/test-data.sh   |
        | VAR33   | /phpunit/Fixtures/test-data.sh   |
        | VAR34   | /phpunit/Fixtures/test-data.sh   |
        | VAR4    | /phpunit/Fixtures/test-data.sh   |
        | VAR5    | /phpunit/Fixtures/test-data.sh   |
        | VAR6    | /phpunit/Fixtures/test-data.sh   |
        | VAR7    | /phpunit/Fixtures/test-data.sh   |
        | VAR8    | /phpunit/Fixtures/test-data.sh   |
        | VAR9    | /phpunit/Fixtures/test-data.sh   |
        | VARENV1 | /phpunit/Fixtures/test-data.sh   |
        | VARENV2 | /phpunit/Fixtures/test-data.sh   |
        | VARENV3 | /phpunit/Fixtures/test-data.sh   |
        | VARENV4 | /phpunit/Fixtures/test-data.sh   |
        EOD,
      ],

      // Wrapped in inline code.
      [
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        | Name      | Default value | Description                                                                                                                                                                                |
        |-----------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
        | `VAR1`    | `UNSET`       |                                                                                                                                                                                            |
        | `VAR10`   | `val10`       | Description without a leading space.                                                                                                                                                       |
        | `VAR11`   | `val11`       | Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, `$VAR9`, VAR10 and VAR12 variable reference.                                               |
        | `VAR12`   | `val12`       | Description without a leading space that goes on<br />multiple lines.<br /><br />And has a comment with no content.                                                                        |
        | `VAR13`   | `val13`       | And has an empty line before it without a content.                                                                                                                                         |
        | `VAR14`   | `val14`       |                                                                                                                                                                                            |
        | `VAR15`   | `val16`       |                                                                                                                                                                                            |
        | `VAR17`   | `val17`       |                                                                                                                                                                                            |
        | `VAR2`    | `val2`        |                                                                                                                                                                                            |
        | `VAR3`    | `val3`        |                                                                                                                                                                                            |
        | `VAR33`   | `VAR32`       |                                                                                                                                                                                            |
        | `VAR34`   | `UNSET`       |                                                                                                                                                                                            |
        | `VAR4`    | `val4`        |                                                                                                                                                                                            |
        | `VAR5`    | `abc`         |                                                                                                                                                                                            |
        | `VAR6`    | `VAR5`        |                                                                                                                                                                                            |
        | `VAR7`    | `VAR5`        |                                                                                                                                                                                            |
        | `VAR8`    | `val8`        |                                                                                                                                                                                            |
        | `VAR9`    | `val9`        | Description with leading space.                                                                                                                                                            |
        | `VARENV1` | `valenv1`     |                                                                                                                                                                                            |
        | `VARENV2` | `UNSET`       |                                                                                                                                                                                            |
        | `VARENV3` | `valenv3`     | Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and testorg/test-package and `testorg/test-package` again. |
        | `VARENV4` | `UNSET`       | Comment 2 from script without a leading space that goes on<br />multiple lines.                                                                                                            |
        EOD,
      ],

      // Wrapped in inline code with numbers.
      [
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        | Name      | Default value | Description                                                                                                                                                                                |
        |-----------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
        | `VAR1`    | `UNSET`       |                                                                                                                                                                                            |
        | `VAR10`   | `val10`       | Description without a leading space.                                                                                                                                                       |
        | `VAR11`   | `val11`       | Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, `$VAR9`, VAR10 and VAR12 variable reference.                                               |
        | `VAR12`   | `val12`       | Description without a leading space that goes on<br />multiple lines.<br /><br />And has a comment with no content.                                                                        |
        | `VAR13`   | `val13`       | And has an empty line before it without a content.                                                                                                                                         |
        | `VAR14`   | `val14`       |                                                                                                                                                                                            |
        | `VAR15`   | `val16`       |                                                                                                                                                                                            |
        | `VAR17`   | `val17`       |                                                                                                                                                                                            |
        | `VAR2`    | `val2`        |                                                                                                                                                                                            |
        | `VAR3`    | `val3`        |                                                                                                                                                                                            |
        | `VAR33`   | `VAR32`       |                                                                                                                                                                                            |
        | `VAR34`   | `UNSET`       |                                                                                                                                                                                            |
        | `VAR4`    | `val4`        |                                                                                                                                                                                            |
        | `VAR5`    | `abc`         |                                                                                                                                                                                            |
        | `VAR6`    | `VAR5`        |                                                                                                                                                                                            |
        | `VAR7`    | `VAR5`        |                                                                                                                                                                                            |
        | `VAR8`    | `val8`        |                                                                                                                                                                                            |
        | `VAR9`    | `val9`        | Description with leading space.                                                                                                                                                            |
        | `VARENV1` | `valenv1`     |                                                                                                                                                                                            |
        | `VARENV2` | `UNSET`       |                                                                                                                                                                                            |
        | `VARENV3` | `valenv3`     | Comment from script with reference to composer.lock and `composer.lock` again and somespecialtoken and `somespecialtoken` again and testorg/test-package and `testorg/test-package` again. |
        | `VARENV4` | `UNSET`       | Comment `2` from script without a leading space that goes on<br />multiple lines.                                                                                                          |
        EOD,
      ],

      // Wrapped in inline code with extras.
      [
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-table',
          '--sort' => TRUE,
          '--md-no-inline-code-wrap-numbers' => TRUE,
          '--md-inline-code-extra-file' => [self::fixtureFile('test-data-ticks-included.txt')],
          'paths' => [self::fixtureFile('test-data.sh')],
        ],
        <<<'EOD'
        | Name      | Default value | Description                                                                                                                                                                                      |
        |-----------|---------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
        | `VAR1`    | `UNSET`       |                                                                                                                                                                                                  |
        | `VAR10`   | `val10`       | Description without a leading space.                                                                                                                                                             |
        | `VAR11`   | `val11`       | Description without a leading space that goes on<br />multiple lines and has a `VAR7`, `$VAR8`, `$VAR9`, VAR10 and VAR12 variable reference.                                                     |
        | `VAR12`   | `val12`       | Description without a leading space that goes on<br />multiple lines.<br /><br />And has a comment with no content.                                                                              |
        | `VAR13`   | `val13`       | And has an empty line before it without a content.                                                                                                                                               |
        | `VAR14`   | `val14`       |                                                                                                                                                                                                  |
        | `VAR15`   | `val16`       |                                                                                                                                                                                                  |
        | `VAR17`   | `val17`       |                                                                                                                                                                                                  |
        | `VAR2`    | `val2`        |                                                                                                                                                                                                  |
        | `VAR3`    | `val3`        |                                                                                                                                                                                                  |
        | `VAR33`   | `VAR32`       |                                                                                                                                                                                                  |
        | `VAR34`   | `UNSET`       |                                                                                                                                                                                                  |
        | `VAR4`    | `val4`        |                                                                                                                                                                                                  |
        | `VAR5`    | `abc`         |                                                                                                                                                                                                  |
        | `VAR6`    | `VAR5`        |                                                                                                                                                                                                  |
        | `VAR7`    | `VAR5`        |                                                                                                                                                                                                  |
        | `VAR8`    | `val8`        |                                                                                                                                                                                                  |
        | `VAR9`    | `val9`        | Description with leading space.                                                                                                                                                                  |
        | `VARENV1` | `valenv1`     |                                                                                                                                                                                                  |
        | `VARENV2` | `UNSET`       |                                                                                                                                                                                                  |
        | `VARENV3` | `valenv3`     | Comment from script with reference to `composer.lock` and `composer.lock` again and `somespecialtoken` and `somespecialtoken` again and `testorg/test-package` and `testorg/test-package` again. |
        | `VARENV4` | `UNSET`       | Comment 2 from script without a leading space that goes on<br />multiple lines.                                                                                                                  |
        EOD,
      ],
    ];
  }

}

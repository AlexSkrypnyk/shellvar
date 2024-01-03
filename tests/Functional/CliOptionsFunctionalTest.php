<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Tests\Functional;

/**
 * Class CliOptionsFunctionalTest.
 *
 * Functional tests for CLI options.
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 *
 * @coversDefaultClass \AlexSkrypnyk\ShellVariablesExtractor\Command\VariablesExtractorCommand
 */
class CliOptionsFunctionalTest extends FunctionalTestBase {

  /**
   * @covers ::execute
   * @dataProvider dataProviderMain
   * @runInSeparateProcess
   */
  public function testMain(array|string $args, string|int $expected_code, string $expected_output) : void {
    $args = is_array($args) ? $args : [$args];
    $result = $this->runExecute($args);
    $this->assertEquals($expected_code, $result['code']);
    $this->assertStringContainsString($expected_output, $result['output']);
  }

  public static function dataProviderMain() : array {
    return [
      [
        ['--help' => TRUE],
        0,
        'Extract variables from shell scripts.',
      ],
      [
        ['-h' => TRUE],
        0,
        'Extract variables from shell scripts.',
      ],
    ];
  }

}

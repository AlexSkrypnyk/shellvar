<?php

/**
 * Class ExampleScriptUnitTest.
 *
 * Unit tests for script.php.
 *
 * @group scripts
 *
 * phpcs:disable Drupal.Commenting.DocComment.MissingShort
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */
class ExampleScriptUnitTest extends ScriptUnitTestBase {

  /**
   * {@inheritdoc}
   */
  protected $script = 'extract-shell-variables.php';

  /**
   * @dataProvider dataProviderMain
   * @runInSeparateProcess
   */
  public function testMain($args, $expected_code, $expected_output) {
    $args = is_array($args) ? $args : [$args];
    $result = $this->runScript($args, TRUE);
    $this->assertEquals($expected_code, $result['code']);
    $this->assertStringContainsString($expected_output, $result['output']);
  }

  public function dataProviderMain() {
    return [
      [
        '--help',
        0,
        'Extract variables from shell scripts.',
      ],
      [
        '-help',
        0,
        'Extract variables from shell scripts.',
      ],
      [
        '-h',
        0,
        'Extract variables from shell scripts.',
      ],
      [
        '-?',
        0,
        'Extract variables from shell scripts.',
      ],
      [
        [],
        1,
        'Extract variables from shell scripts.',
      ],
    ];
  }

}

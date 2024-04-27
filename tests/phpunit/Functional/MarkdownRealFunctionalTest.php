<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\Command\ExtractCommand;

/**
 * Class RealFunctionalTest.
 *
 * Functional tests for real file.
 */
class MarkdownRealFunctionalTest extends FunctionalTestCase {

  /**
   * The file extension.
   */
  public static string $extension = '.md';

  /**
   * Test real file.
   *
   * @dataProvider dataProviderRealFile
   * @covers \AlexSkrypnyk\Shellvar\Extractor\ShellExtractor
   * @runInSeparateProcess
   * @group real
   */
  public function testRealFile(array|string $input): void {
    $input = is_array($input) ? $input : [$input];
    $result = $this->runExecute(ExtractCommand::class, $input);
    $actual = implode(PHP_EOL, $result);

    if (!empty(getenv('UPDATE_FIXTURES'))) {
      $this->fixtureExpectationDataProviderFilePutContents($actual, static::$extension);
    }

    $expected_output = $this->fixtureExpectationDataProviderFileGetContents(static::$extension);
    $this->assertEquals($expected_output, $actual);
  }

  /**
   * Data provider for testRealFile().
   */
  public static function dataProviderRealFile(): array {
    return [
      [
        [
          '--exclude-local' => TRUE,
          '--format' => 'md-blocks',
          'paths' => [self::fixtureFile('test-data-real.sh')],
        ],
      ],
    ];
  }

}

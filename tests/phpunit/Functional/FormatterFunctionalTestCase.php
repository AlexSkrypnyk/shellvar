<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\Command\ExtractCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * Class FormatterFunctionalTestBase.
 *
 * Functional tests for formatters.
 */
#[Group('scripts')]
abstract class FormatterFunctionalTestCase extends FunctionalTestCase {

  /**
   * {@inheritdoc}
   */
  public static string $extension = '.md';

  #[DataProvider('dataProviderFormatter')]
  #[RunInSeparateProcess]
  public function testFormatter(string $message, array|string $input): void {
    $input = is_array($input) ? $input : [$input];
    $result = $this->runExecute(ExtractCommand::class, $input);
    $actual = implode(PHP_EOL, $result);

    if (!empty(getenv('UPDATE_FIXTURES'))) {
      $this->fixtureExpectationDataProviderFilePutContents($actual, static::$extension);
    }

    $expected_output = $this->fixtureExpectationDataProviderFileGetContents(static::$extension);
    $this->assertEquals($expected_output, $actual, $message);
  }

  abstract public static function dataProviderFormatter(): array;

}

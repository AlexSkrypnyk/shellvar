<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Utils;
use Symfony\Component\Console\Exception\InvalidOptionException;

/**
 * Test Utils.
 *
 * @coversDefaultClass \AlexSkrypnyk\Shellvar\Utils
 */
class UtilsTest extends UnitTestBase {

  /**
   * @covers ::getLinesFromFiles
   * @covers ::resolvePath
   */
  public function testUtils(): void {
    $this->assertEquals('', Utils::resolvePath(''));

    $lines = Utils::getLinesFromFiles([$this->fixtureFile('.env')]);
    $this->assertEquals([
      'VARENV1=valenv1_dotenv',
      '',
      'VARENV2=',
      '',
      '# Comment from script.',
      'VARENV3=valenv3-dotenv',
      '',
      '# Comment 2 from .env without a leading space that goes on',
      '# multiple lines.',
      'VARENV4=',
      '',
    ], $lines);

    $lines_removed_empty = Utils::getLinesFromFiles([$this->fixtureFile('.env')], TRUE);
    $this->assertEquals([
      'VARENV1=valenv1_dotenv',
      'VARENV2=',
      '# Comment from script.',
      'VARENV3=valenv3-dotenv',
      '# Comment 2 from .env without a leading space that goes on',
      '# multiple lines.',
      'VARENV4=',
    ], $lines_removed_empty);

    $this->expectException(InvalidOptionException::class);
    Utils::resolvePath('/a-fake-path/' . rand(1, 10) . '.txt');

  }

  /**
   * Test for removeDoubleQuotes.
   *
   * @dataProvider dataProviderRemoveDoubleQuotes
   * @covers ::removeDoubleQuotes
   */
  public function testRemoveDoubleQuotes(string $input, string $expected): void {
    $this->assertEquals($expected, Utils::removeDoubleQuotes($input));
  }

  /**
   * Data provider for testRemoveDoubleQuotes.
   */
  public static function dataProviderRemoveDoubleQuotes(): array {
    return [
      ['Hello "world"', 'Hello world'],
      ['This is a \\"test\\" string', 'This is a \\"test\\" string'],
      ['"Hello" \'world\'', 'Hello \'world\''],
      ['"She said, \'You\'re here.\'"', 'She said, \'You\'re here.\''],
      ['Just a string', 'Just a string'],
    ];
  }

  /**
   * Test for recursiveStrtr().
   *
   * @dataProvider dataProviderRecursiveStrtr
   * @covers ::recursiveStrtr
   */
  public function testRecursiveStrtr(string $input, array $replacements, string|array $expected, ?string $expected_exception_message = NULL): void {
    if ($expected === 'Exception' && is_string($expected_exception_message)) {
      $this->expectException(\Exception::class);
      $this->expectExceptionMessage($expected_exception_message);
    }

    $result = Utils::recursiveStrtr($input, $replacements);

    if ($expected !== 'Exception') {
      $this->assertEquals($expected, $result);
    }
  }

  /**
   * Data provider for testRecursiveStrtr.
   */
  public static function dataProviderRecursiveStrtr(): array {
    return [
      ['Hello {name}', ['{name}' => 'Alice'], 'Hello Alice'],
      ['{greeting} {name}', ['{greeting}' => 'Hi', '{name}' => 'Bob'], 'Hi Bob'],
      ['No placeholders', [], 'No placeholders'],
      ['{item1} {item2}', ['{item1}' => 'Apple', '{item2}' => 'Banana'], 'Apple Banana'],
      ['Nested {placeholder} here', ['{placeholder}' => '{nested}'], 'Nested {nested} here'],
      // Circular reference test cases.
      ['{loop}', ['{loop}' => '{loop}'], 'Exception', 'Circular reference leading back to the original input detected'],
      ['{start}', ['{start}' => '{next}', '{next}' => '{start}'], 'Exception', 'Circular reference leading back to the original input detected'],
      ['{start}', ['{next}' => '{start}', '{start}' => '{next}'], 'Exception', 'Circular reference leading back to the original input detected'],
    ];
  }

}

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

}

<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Config\Config;

/**
 * Unit Config test.
 *
 * @covers \AlexSkrypnyk\Shellvar\Config\Config
 */
class ConfigUnitTest extends UnitTestBase {

  public function testConfig(): void {
    $config = new Config([
      'arg1' => 'valueArg1',
      'arg2' => 'valueArg2',
    ]);

    $this->assertEquals('valueArg1', $config->get('arg1'));
    $this->assertEquals('valueArg2', $config->get('arg2'));

    $config->set('foo', ['hiFoo1', 'hiFoo2']);
    $this->assertEquals(['hiFoo1', 'hiFoo2'], $config->get('foo'));

    $config->setAll(['arg1' => 'valueArg1Updated', 'foo' => 'FooUpdated']);
    $this->assertEquals('FooUpdated', $config->get('foo'));
    $this->assertEquals('valueArg1Updated', $config->get('arg1'));

  }

}

<?php

namespace AlexSkrypnyk\Tests\Unit;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Factory\AutodiscoveryFactory;

/**
 * Class AutodiscoveryFactoryUnitTest.
 *
 * Unit tests for theAutodiscoveryFactory class.
 *
 * @coversDefaultClass \AlexSkrypnyk\ShellVariablesExtractor\Factory\AutodiscoveryFactory
 */
class AutodiscoveryFactoryUnitTest extends UnitTestBase {

  /**
   * Test that the autodiscovery can discover only items set to be discovered.
   *
   * @covers ::__construct
   * @covers ::getEntityClasses
   */
  public function testDiscovery() : void {
    $autodiscovery = new AutodiscoveryFactory('tests/Fixtures');
    $discovered_classes = $autodiscovery->getEntityClasses();
    $this->assertEquals([
      'DummyDiscoverable11' => 'AlexSkrypnyk\Tests\Fixtures\Discovery1\DummyDiscoverable11',
      'DummyDiscoverable12' => 'AlexSkrypnyk\Tests\Fixtures\Discovery1\DummyDiscoverable12',
      'DummyDiscoverable21' => 'AlexSkrypnyk\Tests\Fixtures\Discovery2\DummyDiscoverable21',
      'DummyDiscoverable22' => 'AlexSkrypnyk\Tests\Fixtures\Discovery2\DummyDiscoverable22',
    ], $discovered_classes);
  }

  /**
   * Test that the autodiscovery can discover only items of a certain type.
   *
   * @covers ::discoverOwn
   * @covers ::registerEntityClass
   * @covers ::getEntityClasses
   */
  public function testDiscoveryTyped() : void {
    $autodiscovery = new AutodiscoveryFactory('tests/Fixtures/Discovery1');
    $discovered_classes = $autodiscovery->getEntityClasses();
    $this->assertEquals([
      'DummyDiscoverable11' => 'AlexSkrypnyk\Tests\Fixtures\Discovery1\DummyDiscoverable11',
      'DummyDiscoverable12' => 'AlexSkrypnyk\Tests\Fixtures\Discovery1\DummyDiscoverable12',
    ], $discovered_classes);

    $autodiscovery = new AutodiscoveryFactory('tests/Fixtures/Discovery2');
    $discovered_classes = $autodiscovery->getEntityClasses();
    $this->assertEquals([
      'DummyDiscoverable21' => 'AlexSkrypnyk\Tests\Fixtures\Discovery2\DummyDiscoverable21',
      'DummyDiscoverable22' => 'AlexSkrypnyk\Tests\Fixtures\Discovery2\DummyDiscoverable22',
    ], $discovered_classes);
  }

  /**
   * Test creating a single auto discovered entity.
   *
   * @covers ::create
   */
  public function testCreate() : void {
    $autodiscovery = new AutodiscoveryFactory('tests/Fixtures');
    $discovered = $autodiscovery->create('DummyDiscoverable11', new Config());
    // @phpstan-ignore-next-line
    $this->assertEquals('DummyDiscoverable11', $discovered::getName());
    $discovered = $autodiscovery->create('DummyDiscoverable12', new Config());
    // @phpstan-ignore-next-line
    $this->assertEquals('DummyDiscoverable12', $discovered::getName());
  }

  /**
   * Test creating all auto discovered entities.
   *
   * @covers ::createAll
   */
  public function testCreateAll() : void {
    $config = new Config();
    $autodiscovery = new AutodiscoveryFactory('tests/Fixtures');
    $discovered_all = $autodiscovery->createAll($config);
    $this->assertCount(4, $discovered_all);
    usort($discovered_all, function ($a, $b) {
      return strcmp($a::getName(), $b::getName());
    });
    $this->assertEquals('DummyDiscoverable11', $discovered_all[0]::getName());
    $this->assertEquals('DummyDiscoverable12', $discovered_all[1]::getName());
    $this->assertEquals('DummyDiscoverable21', $discovered_all[2]::getName());
    $this->assertEquals('DummyDiscoverable22', $discovered_all[3]::getName());
  }

  /**
   * Test that exception is thrown when an invalid autodiscovery is requested.
   *
   * @covers ::create
   */
  public function testException() : void {
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Invalid entity: non-existent');
    $autodiscovery = new AutodiscoveryFactory('tests/Fixtures');
    $autodiscovery->create('non-existent', new Config());
  }

}

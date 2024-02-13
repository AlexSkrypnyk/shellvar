<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Factory\AutodiscoveryFactory;
use AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery1\DummyDiscoverable11;
use AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery1\DummyDiscoverable12;
use AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery2\DummyDiscoverable21;
use AlexSkrypnyk\Shellvar\Tests\Fixtures\Discovery2\DummyDiscoverable22;

/**
 * Class AutodiscoveryFactoryUnitTest.
 *
 * Unit tests for theAutodiscoveryFactory class.
 *
 * @coversDefaultClass \AlexSkrypnyk\Shellvar\Factory\AutodiscoveryFactory
 */
class AutodiscoveryFactoryUnitTest extends UnitTestBase {

  /**
   * Test that the autodiscovery can discover only items set to be discovered.
   *
   * @covers ::__construct
   * @covers ::getEntityClasses
   */
  public function testDiscovery() : void {
    $autodiscovery = new AutodiscoveryFactory('tests/phpunit/Fixtures');
    $discovered_classes = $autodiscovery->getEntityClasses();
    $this->assertEquals([
      'DummyDiscoverable11' => DummyDiscoverable11::class,
      'DummyDiscoverable12' => DummyDiscoverable12::class,
      'DummyDiscoverable21' => DummyDiscoverable21::class,
      'DummyDiscoverable22' => DummyDiscoverable22::class,
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
    $autodiscovery = new AutodiscoveryFactory('tests/phpunit/Fixtures/Discovery1');
    $discovered_classes = $autodiscovery->getEntityClasses();
    $this->assertEquals([
      'DummyDiscoverable11' => DummyDiscoverable11::class,
      'DummyDiscoverable12' => DummyDiscoverable12::class,
    ], $discovered_classes);

    $autodiscovery = new AutodiscoveryFactory('tests/phpunit/Fixtures/Discovery2');
    $discovered_classes = $autodiscovery->getEntityClasses();
    $this->assertEquals([
      'DummyDiscoverable21' => DummyDiscoverable21::class,
      'DummyDiscoverable22' => DummyDiscoverable22::class,
    ], $discovered_classes);
  }

  /**
   * Test creating a single auto discovered entity.
   *
   * @covers ::create
   */
  public function testCreate() : void {
    $autodiscovery = new AutodiscoveryFactory('tests/phpunit/Fixtures');
    $discovered = $autodiscovery->create('DummyDiscoverable11', new Config());
    $this->assertEquals('DummyDiscoverable11', $discovered::getName());
    $discovered = $autodiscovery->create('DummyDiscoverable12', new Config());
    $this->assertEquals('DummyDiscoverable12', $discovered::getName());
  }

  /**
   * Test creating all auto discovered entities.
   *
   * @covers ::createAll
   */
  public function testCreateAll() : void {
    $config = new Config();
    $autodiscovery = new AutodiscoveryFactory('tests/phpunit/Fixtures');
    $discovered_all = $autodiscovery->createAll($config);
    $this->assertCount(4, $discovered_all);
    usort($discovered_all, static function ($a, $b) : int {
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
    $autodiscovery = new AutodiscoveryFactory('tests/phpunit/Fixtures');
    $autodiscovery->create('non-existent', new Config());
  }

}

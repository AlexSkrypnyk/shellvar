<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Unit;

use DrevOps\Shellvar\AbstractManager;
use DrevOps\Shellvar\Factory\AutodiscoveryFactory;
use DrevOps\Shellvar\Tests\Fixtures\ConsoleAware\DummyConsoleAware1;
use DrevOps\Shellvar\Tests\Fixtures\ConsoleAware\DummyConsoleAware2;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Test AbstractManager.
 */
#[CoversClass(AbstractManager::class)]
class AbstractManagerUnitTest extends UnitTestBase {

  /**
   * Test that console definitions are collected from all discovered classes.
   *
   * @param string $method
   *   The manager method to call.
   * @param array<string, class-string> $classes
   *   Discovered classes keyed by entity name.
   * @param array<int, string> $expected
   *   Expected definition names in order.
   */
  #[DataProvider('dataProviderGetAllConsoleDefinitions')]
  public function testGetAllConsoleDefinitions(string $method, array $classes, array $expected): void {
    $factory = $this->createStub(AutodiscoveryFactory::class);
    $factory->method('getEntityClasses')->willReturn($classes);

    $manager = new class($factory) extends AbstractManager {

      public function __construct(AutodiscoveryFactory $factory) {
        $this->factory = $factory;
      }

      public static function getConsoleArguments(): array {
        return [new InputArgument('argument_manager', InputArgument::OPTIONAL)];
      }

      public static function getConsoleOptions(): array {
        return [new InputOption('option_manager', NULL, InputOption::VALUE_NONE)];
      }

      protected static function getDiscoveryDirectory(): string {
        return '';
      }

    };

    $actual = array_map(static fn(InputArgument|InputOption $item): string => $item->getName(), $manager->{$method}());

    $this->assertSame($expected, $actual);
  }

  /**
   * Data provider for testGetAllConsoleDefinitions.
   */
  public static function dataProviderGetAllConsoleDefinitions(): array {
    $one = ['DummyConsoleAware1' => DummyConsoleAware1::class];
    $two = ['DummyConsoleAware1' => DummyConsoleAware1::class, 'DummyConsoleAware2' => DummyConsoleAware2::class];

    return [
      'arguments, no classes' => ['getAllConsoleArguments', [], ['argument_manager']],
      'arguments, 1 class' => ['getAllConsoleArguments', $one, ['argument11', 'argument_manager']],
      'arguments, 2 classes' => ['getAllConsoleArguments', $two, ['argument11', 'argument21', 'argument22', 'argument_manager']],
      'options, no classes' => ['getAllConsoleOptions', [], ['option_manager']],
      'options, 1 class' => ['getAllConsoleOptions', $one, ['option11', 'option_manager']],
      'options, 2 classes' => ['getAllConsoleOptions', $two, ['option11', 'option21', 'option22', 'option_manager']],
    ];
  }

}

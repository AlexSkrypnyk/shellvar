<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Functional;

use AlexSkrypnyk\Shellvar\Tests\Traits\AssertTrait;
use AlexSkrypnyk\Shellvar\Tests\Traits\FixtureTrait;
use AlexSkrypnyk\Shellvar\Tests\Traits\MockTrait;
use AlexSkrypnyk\Shellvar\Tests\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class FunctionalTestCase.
 *
 * Base class to test commands.
 */
abstract class FunctionalTestCase extends TestCase {

  use AssertTrait;
  use ReflectionTrait;
  use MockTrait;
  use FixtureTrait;

  /**
   * CommandTester instance.
   *
   * @var \Symfony\Component\Console\Tester\CommandTester
   */
  protected $commandTester;

  /**
   * Run main() with optional arguments.
   *
   * @param string|object $object_or_class
   *   Object or class name.
   * @param array<mixed> $input
   *   Optional array of input arguments.
   * @param array<string, string> $options
   *   Optional array of options. See CommandTester::execute() for details.
   *
   * @return array<string>
   *   Array of output lines.
   */
  protected function runExecute(string|object $object_or_class, array $input = [], array $options = []): array {
    $application = new Application();
    /** @var \Symfony\Component\Console\Command\Command $instance */
    $instance = is_object($object_or_class) ? $object_or_class : new $object_or_class();
    $application->add($instance);

    /** @var string $name */
    $name = $instance->getName();
    $command = $application->find($name);
    $this->commandTester = new CommandTester($command);

    $this->commandTester->execute($input, $options);

    return explode(PHP_EOL, $this->commandTester->getDisplay());
  }

}

<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Config\ConfigAwareTrait;
use AlexSkrypnyk\Shellvar\Factory\AutodiscoveryFactory;
use AlexSkrypnyk\Shellvar\Traits\SingletonInterface;
use AlexSkrypnyk\Shellvar\Traits\SingletonTrait;

/**
 * Class AbstractManager.
 *
 * Provides generic functionality for all managers.
 */
abstract class AbstractManager implements SingletonInterface, ConsoleAwareInterface {

  use SingletonTrait;
  use ConfigAwareTrait;

  /**
   * The entity factory.
   */
  protected AutodiscoveryFactory $factory;

  /**
   * AbstractManager constructor.
   *
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   The configuration.
   */
  public function __construct(Config $config) {
    $this->setConfig($config);

    $this->factory = new AutodiscoveryFactory(static::getDiscoveryDirectory());
  }

  /**
   * Get the directory to discover classes in.
   */
  abstract protected static function getDiscoveryDirectory(): string;

  /**
   * {@inheritdoc}
   */
  public static function getConsoleArguments(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return [];
  }

  /**
   * Get all console arguments from all discovered classes.
   *
   * @return \Symfony\Component\Console\Input\InputArgument[]
   *   Array of console arguments.
   */
  public function getAllConsoleArguments(): array {
    $items = [];

    /** @var \AlexSkrypnyk\Shellvar\ConsoleAwareInterface $class */
    foreach ($this->factory->getEntityClasses() as $class) {
      $items = array_merge($class::getConsoleArguments());
    }

    return array_merge($items, $this::getConsoleArguments());
  }

  /**
   * Get all console options from all discovered classes.
   *
   * @return \Symfony\Component\Console\Input\InputOption[]
   *   Array of console options.
   */
  public function getAllConsoleOptions(): array {
    $items = [];

    /** @var \AlexSkrypnyk\Shellvar\ConsoleAwareInterface $class */
    foreach ($this->factory->getEntityClasses() as $class) {
      $items = array_merge($items, $class::getConsoleOptions());
    }

    return array_merge($items, $this::getConsoleOptions());
  }

}

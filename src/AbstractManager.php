<?php

namespace AlexSkrypnyk\ShellVariablesExtractor;

use AlexSkrypnyk\ShellVariablesExtractor\Config\ConfigAwareTrait;
use AlexSkrypnyk\ShellVariablesExtractor\Factory\AutodiscoveryFactory;
use AlexSkrypnyk\ShellVariablesExtractor\Traits\SingletonInterface;
use AlexSkrypnyk\ShellVariablesExtractor\Traits\SingletonTrait;

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
   *
   * @var \AlexSkrypnyk\ShellVariablesExtractor\Factory\AutodiscoveryFactory
   */
  protected $factory;

  /**
   * AbstractManager constructor.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
   *   The configuration.
   */
  public function __construct($config) {
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

    foreach ($this->factory->getEntityClasses() as $class) {
      $items = array_merge($class::getConsoleArguments());
    }
    $items = array_merge($items, $this::getConsoleArguments());

    return $items;
  }

  /**
   * Get all console options from all discovered classes.
   *
   * @return \Symfony\Component\Console\Input\InputOption[]
   *   Array of console options.
   */
  public function getAllConsoleOptions(): array {
    $items = [];

    foreach ($this->factory->getEntityClasses() as $class) {
      $items = array_merge($items, $class::getConsoleOptions());
    }
    $items = array_merge($items, $this::getConsoleOptions());

    return $items;
  }

}

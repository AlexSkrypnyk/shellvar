<?php

declare(strict_types=1);

namespace DrevOps\Shellvar;

use DrevOps\Shellvar\Config\Config;
use DrevOps\Shellvar\Config\ConfigAwareTrait;
use DrevOps\Shellvar\Factory\AutodiscoveryFactory;
use DrevOps\Shellvar\Traits\SingletonInterface;
use DrevOps\Shellvar\Traits\SingletonTrait;

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
   * @param \DrevOps\Shellvar\Config\Config $config
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

    /** @var \DrevOps\Shellvar\ConsoleAwareInterface $class */
    foreach ($this->factory->getEntityClasses() as $class) {
      $items = array_merge($items, $class::getConsoleArguments());
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

    /** @var \DrevOps\Shellvar\ConsoleAwareInterface $class */
    foreach ($this->factory->getEntityClasses() as $class) {
      $items = array_merge($items, $class::getConsoleOptions());
    }

    return array_merge($items, $this::getConsoleOptions());
  }

}

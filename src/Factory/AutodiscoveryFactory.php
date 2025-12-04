<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Factory;

use AlexSkrypnyk\Shellvar\Config\Config;

/**
 * Class AutodiscoveryFactory.
 *
 * This code automatically identifies classes in a given directory that
 * implement the FactoryDiscoverableInterface. It also provides the option to
 * manually include additional classes and generates instances of all the
 * identified classes.
 */
class AutodiscoveryFactory {

  /**
   * Array of entity classes.
   *
   * @var array<string>
   */
  protected array $classes = [];

  /**
   * Constructor.
   *
   * @param string $dir
   *   Directory to scan for entities.
   */
  public function __construct(protected string $dir) {
    // Automatically discover own classes provided with a package.
    static::discoverOwn();
  }

  /**
   * Create class instance from discovered classes by entity name.
   *
   * @param string $name
   *   Entity name.
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   Entity configuration.
   *
   * @return \AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface
   *   Instantiated entity class instance.
   *
   * @throws \Exception
   *   If the requested entity does not exist.
   */
  public function create(string $name, Config $config): FactoryDiscoverableInterface {
    if (!isset($this->classes[$name])) {
      throw new \Exception('Invalid entity: ' . $name);
    }

    $instance = new $this->classes[$name]($config);

    if (!$instance instanceof FactoryDiscoverableInterface) {
      // @codeCoverageIgnoreStart
      throw new \Exception('Invalid entity: ' . $name);
      // @codeCoverageIgnoreEnd
    }

    return $instance;
  }

  /**
   * Create all class instances.
   *
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   The configuration instance.
   *
   * @return \AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface[]
   *   Array of instantiated entity class instances.
   *
   * @throws \Exception
   */
  public function createAll(Config $config): array {
    $instances = [];

    /** @var \AlexSkrypnyk\Shellvar\Factory\FactoryDiscoverableInterface $class */
    foreach ($this->classes as $class) {
      $instances[] = $this->create($class::getName(), $config);
    }

    return $instances;
  }

  /**
   * Discover own entities provided with this package.
   *
   * Extending classes can use FormatterFactory::registerFormatter() to register
   * their own.
   */
  protected function discoverOwn(): void {
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->dir));
    $php_files = new \RegexIterator($iterator, '/\.php$/');

    /** @var \SplFileInfo $php_file */
    foreach ($php_files as $php_file) {
      require_once $php_file->getPathname();
      // Use the last part of the entity namespace as the entity type.
      $entity_type = $php_file->getPathInfo()->getFilename();
      foreach (get_declared_classes() as $class) {
        $reflection = new \ReflectionClass($class);
        if ($reflection->isUserDefined()
          && $reflection->isInstantiable()
          && !$reflection->isAbstract()
          && $reflection->implementsInterface(FactoryDiscoverableInterface::class)
          && str_ends_with($reflection->getNamespaceName(), $entity_type)
        ) {
          $this->registerEntityClass($class::getName(), $class);
        }
      }
    }
  }

  /**
   * Register an entity class.
   *
   * @param string $name
   *   Formatter name.
   * @param string $class
   *   Formatter class.
   */
  public function registerEntityClass(string $name, string $class): void {
    $this->classes[$name] = $class;
  }

  /**
   * Get all discovered entity classes.
   *
   * @return array<mixed|object>
   *   Array of entity classes.
   */
  public function getEntityClasses(): array {
    return $this->classes;
  }

}

<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Factory;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;

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
   * @var string
   */
  protected $classes = [];

  /**
   * Directory to scan for entities.
   *
   * @var string
   */
  protected $dir;

  /**
   * Constructor.
   *
   * @param string $dir
   *   Directory to scan for entities.
   */
  public function __construct($dir) {
    $this->dir = $dir;

    // Automatically discover own classes provided with a package.
    static::discoverOwn();
  }

  /**
   * Create class instance from discovered classes by entity name.
   *
   * @param string $name
   *   Entity name.
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
   *   Formatter configuration.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface
   *   Instantiated entity class instance.
   *
   * @throws \Exception
   *   If the requested entity does not exist.
   */
  public function create(string $name, Config $config) {
    if (!isset($this->classes[$name])) {
      throw new \Exception("Invalid entity: $name");
    }

    return new $this->classes[$name]($config);
  }

  /**
   * Create all class instances.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Config\Config $config
   *   The configuration instance.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Factory\FactoryDiscoverableInterface[]
   *   Array of instantiated entity class instances.
   */
  public function createAll(Config $config) {
    $instances = [];

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
    $phpFiles = new \RegexIterator($iterator, '/\.php$/');

    /** @var \SplFileInfo $phpFile */
    foreach ($phpFiles as $phpFile) {
      require_once $phpFile->getPathname();
      // Use the last part of the entity namespace as the entity type.
      $entity_type = $phpFile->getPathInfo()->getFilename();
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
   * @return array
   *   Array of entity classes.
   */
  public function getEntityClasses() {
    return $this->classes;
  }

}

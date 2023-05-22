<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

/**
 * Class FormatterFactory.
 *
 * Factory to create formatters.
 */
class FormatterFactory {

  /**
   * Array of formatters.
   *
   * @var \AlexSkrypnyk\ShellVariablesExtractor\Formatter\FormatterInterface[]
   */
  protected static $formatters = [];

  /**
   * Create formatter by name.
   *
   * @param string $name
   *   Formatter name.
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[] $variables
   *   Array of variables.
   * @param array $config
   *   Formatter configuration.
   *
   * @return \AlexSkrypnyk\ShellVariablesExtractor\Formatter\FormatterInterface
   *   Formatter instance.
   *
   * @throws \Exception
   *   If the requested formatter does not exist.
   */
  public static function create(string $name, array $variables, array $config): FormatterInterface {
    // Automatically discover formatters.
    // Extending classes can use FormatterFactory::registerFormatter() to
    // register their own.
    static::discoverOwnFormatters();

    // Create formatter.
    $name = strtolower($name);
    if (!isset(static::$formatters[$name])) {
      throw new \Exception("Invalid formatter: $name");
    }

    return new static::$formatters[$name]($variables, $config);
  }

  /**
   * Reset instance.
   */
  public static function reset() {
    foreach (array_keys(static::$formatters) as $k) {
      unset(static::$formatters[$k]);
    }
  }

  /**
   * Register a formatter.
   *
   * @param string $name
   *   Formatter name.
   * @param string $class
   *   Formatter class.
   */
  public static function registerFormatter(string $name, string $class): void {
    self::$formatters[$name] = $class;
  }

  /**
   * Discover own formatters provided with this package.
   *
   * Extending classes can use FormatterFactory::registerFormatter() to register
   * their own.
   */
  protected static function discoverOwnFormatters($dir = __DIR__): void {
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
    $phpFiles = new \RegexIterator($iterator, '/\.php$/');

    foreach ($phpFiles as $phpFile) {
      require_once $phpFile->getPathname();
      $class = __NAMESPACE__ . '\\' . $phpFile->getBasename('.php');
      $reflection = new \ReflectionClass($class);
      $interfaces = $reflection->getInterfaceNames();
      if (!$reflection->isAbstract() && in_array(FormatterInterface::class, $interfaces)) {
        self::$formatters[$class::getName()] = $class;
      }
    }
  }

}

<?php

namespace AlexSkrypnyk\ShellVariablesExtractor;

use Symfony\Component\Console\Exception\InvalidOptionException;

/**
 * Class Utils.
 *
 * Utility functions.
 */
class Utils {

  /**
   * Get lines from files.
   *
   * @param array<string> $paths
   *   A list of paths to files.
   * @param bool $remove_empty
   *   Whether to remove empty lines.
   *
   * @return array<string>
   *   A list of lines, merged into one array.
   */
  public static function getLinesFromFiles(array $paths, $remove_empty = FALSE) : array {
    $lines = [];

    foreach ($paths as $path) {
      // @phpstan-ignore-next-line
      $lines = array_merge($lines, preg_split("/(\r\n|\n|\r)/", file_get_contents($path)));
    }

    return $remove_empty ? array_filter($lines) : $lines;
  }

  /**
   * Get non-empty lines from files.
   *
   * @param array<string> $paths
   *   A list of paths to files.
   *
   * @return array<string>
   *   A list of lines, merged into one array.
   */
  public static function getNonEmptyLinesFromFiles(array $paths) : array {
    return static::getLinesFromFiles($paths, TRUE);
  }

  /**
   * Resolve paths.
   *
   * @param array<string> $paths
   *   A list of paths to resolve.
   *
   * @return array<string>
   *   A list of resolved paths.
   */
  public static function resolvePaths(array $paths): array {
    $resolved_paths = [];

    foreach ($paths as $path) {
      $resolved_paths[] = static::resolvePath($path);
    }

    return $resolved_paths;
  }

  /**
   * Resolve a path.
   *
   * @param string $path
   *   A path to resolve.
   *
   * @return string
   *   A resolved path.
   *
   * @throws \Symfony\Component\Console\Exception\InvalidOptionException
   *   If resolved path is not readable.
   */
  public static function resolvePath($path) {
    if (empty($path)) {
      return $path;
    }

    if (!str_starts_with($path, './') && !str_starts_with($path, '/')) {
      $path = getcwd() . DIRECTORY_SEPARATOR . $path;
    }

    if (!is_readable($path)) {
      throw new InvalidOptionException(sprintf('Unable to read a file path %s.', $path));
    }

    return $path;
  }

}

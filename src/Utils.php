<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar;

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
  public static function getLinesFromFiles(array $paths, bool $remove_empty = FALSE): array {
    $lines = [];

    foreach ($paths as $path) {
      $content = file_get_contents($path);

      if ($content === FALSE) {
        throw new InvalidOptionException(sprintf('Unable to read a file path %s.', $path));
      }

      $new_lines = preg_split("/(\r\n|\n|\r)/", $content);

      if ($new_lines === FALSE) {
        throw new InvalidOptionException(sprintf('Unable to split file content into lines for a file path %s.', $path));
      }

      $lines = array_merge($lines, $new_lines);
    }

    return $remove_empty ? array_values(array_filter($lines)) : $lines;
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
  public static function getNonEmptyLinesFromFiles(array $paths): array {
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
  public static function resolvePath(string $path): string {
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

  /**
   * Remove double quotes from a string.
   *
   * @param string $string
   *   A string to remove double quotes from.
   *
   * @return string
   *   A string without double quotes.
   */
  public static function removeDoubleQuotes($string): string {
    $replaced = preg_replace_callback(
      '/
        \\\\"|      # Match escaped double quotes
        \'\[^\'\]*\'|  # Match single-quoted strings
        "|           # Match double quotes
    /x',
      static function (array $matches): string {
        // If the match is a double quote, remove it.
        return ($matches[0] === '"' ? '' : $matches[0]);
      },
      $string
    );

    return $replaced ?? $string;
  }

  /**
   * Replace placeholders in a string.
   *
   * @param string $data
   *   A data to replace placeholders in.
   * @param array<string, string> $replacements
   *   A list of replacements.
   * @param array<string,bool> $visited
   *   A list of visited data. Internal.
   * @param null $original_data
   *   Original data reference. Internal.
   *
   * @return string
   *   A string with replaced placeholders.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  public static function recursiveStrtr(string $data, array $replacements, array &$visited = [], &$original_data = NULL): string {
    if (empty($replacements) && is_string($data)) {
      return $data;
    }

    if ($original_data === NULL) {
      $original_data = $data;
    }

    if (isset($visited[$data])) {
      throw new \Exception(sprintf("Circular reference detected for '%s'", $data));
    }

    $visited[$data] = TRUE;

    $processed = $data;
    do {
      $last = $processed;
      $processed = strtr($processed, $replacements);

      if ($processed === $original_data) {
        throw new \Exception("Circular reference leading back to the original input detected.");
      }
    } while ($last !== $processed);

    return $processed;
  }

}

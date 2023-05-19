#!/usr/bin/env php
<?php

/**
 * @file
 * Scan a file or a directory with shell scripts and extract all variables.
 *
 * Variables can have descriptions and default values that will be printed out
 * to the STDOUT in a CSV format as `name, default_value, description`.
 *
 * This is helpful to maintain a table of variables and their descriptions in
 * documentation.
 *
 * Usage:
 * ./shell-variable-extractor.php path/to/file1 path/to/file2
 * ./shell-variable-extractor.php path/to/dir
 *
 * With excluded file:
 * ./shell-variable-extractor.php -e ../excluded.txt path/to/file
 *
 * Full:
 * ./shell-variable-extractor.php  -t -m -e ./excluded.txt -u "<NOT SET>" ../
 *
 * phpcs:disable Drupal.Commenting.InlineComment.SpacingBefore
 * phpcs:disable Drupal.Commenting.InlineComment.SpacingAfter
 * phpcs:disable Drupal.Classes.ClassFileName.NoMatch
 * phpcs:disable Drupal.Commenting.FunctionComment.Missing
 */

/**
 * Defines exit codes.
 */
define('EXIT_SUCCESS', 0);
define('EXIT_ERROR', 1);

/**
 * Defines error level to be reported as an error.
 */
define('ERROR_LEVEL', E_USER_WARNING);

/**
 * Main functionality.
 */
function main(array $argv, $argc) {
  if (in_array($argv[1] ?? NULL, ['--help', '-help', '-h', '-?'])) {
    print_help();

    return EXIT_SUCCESS;
  }

  // Show help if not enough or more than required arguments.
  if ($argc < 2) {
    print_help();

    return EXIT_ERROR;
  }

  init_cli_args_and_options($argv, $argc);

  $files = get_targets(get_config('paths'));

  if (get_config('debug')) {
    print "Scanning files:\n" . implode("\n", $files) . "\n";
  }

  $all_variables = [];
  foreach ($files as $file) {
    $all_variables += extract_variables_from_file($file);
  }

  // Exclude local variables, if set.
  if (get_config('filter_global')) {
    $all_variables = array_filter($all_variables, function ($value) {
      return preg_match('/^[A-Z0-9_]+$/', $value['name']);
    });
  }

  $exclude_file = get_config('exclude_file');
  if ($exclude_file) {
    $excluded_variables = array_filter(explode("\n", file_get_contents($exclude_file)));
    $all_variables = array_diff_key($all_variables, array_flip($excluded_variables));
  }

  $filter_prefix = get_config('filter_prefix');
  if ($filter_prefix) {
    $all_variables = array_filter($all_variables, function ($value) use ($filter_prefix) {
      return strpos($value['name'], $filter_prefix) !== 0;
    });
  }

  ksort($all_variables);

  if (get_config('ticks')) {
    $all_variables = process_description_ticks($all_variables);
  }

  // Exclude non-assignments.
  array_walk($all_variables, function (&$value) {
    if ($value['is_assignment']) {
      unset($value['is_assignment']);

      return;
    }
    $value = FALSE;
  });
  $all_variables = array_filter($all_variables);

  if (get_config('markdown') == 'table') {
    $csv = render_variables_data($all_variables);
    $csvTable = new CSVTable($csv, get_config('csv_delim'));
    print $csvTable->getMarkup();
  }
  elseif (get_config('markdown')) {
    $markdown_blocks = new MarkdownBlocks($all_variables, get_config('markdown'));
    print $markdown_blocks->getMarkup();
  }
  else {
    print render_variables_data($all_variables);
  }

  return EXIT_SUCCESS;
}

/**
 * Initialise CLI options.
 */
function init_cli_args_and_options($argv, $argc) {
  $opts = [
    'debug' => 'd',
    'exclude-file:' => 'e:',
    'markdown::' => 'm::',
    'csv-delim:' => 'c:',
    'ticks' => 't',
    'ticks-list:' => 'l:',
    'slugify' => 's',
    'unset:' => 'u:',
    'filter-prefix' => 'p',
    'filter-global' => 'g',
  ];

  $optind = 0;
  $options = getopt(implode('', $opts), array_keys($opts), $optind);

  foreach ($opts as $longopt => $shortopt) {
    $longopt = str_replace(':', '', $longopt);
    $shortopt = str_replace(':', '', $shortopt);

    if (isset($options[$shortopt])) {
      $options[$longopt] = $options[$shortopt] === FALSE ? TRUE : $options[$shortopt];
      unset($options[$shortopt]);
    }
    elseif (isset($options[$longopt])) {
      $options[$longopt] = $options[$longopt] === FALSE ? TRUE : $options[$longopt];
    }
  }

  $options += [
    'paths' => '',
    'debug' => FALSE,
    'exclude-file' => FALSE,
    'markdown' => FALSE,
    'ticks' => FALSE,
    'ticks-list' => FALSE,
    'slugify' => FALSE,
    'filter-prefix' => '',
    'filter-global' => '',
    'unset' => '<UNSET>',
    'csv-delim' => ';',
  ];

  $pos_args = array_slice($argv, $optind);
  $pos_args = array_filter($pos_args);

  if (count($pos_args) < 1) {
    die('ERROR At least one path to a file or a directory is required.');
  }

  $paths = $pos_args;

  foreach ($paths as $k => $path) {
    if (strpos($path, './') !== 0 && strpos($path, '/') !== 0) {
      $paths[$k] = realpath(getcwd() . DIRECTORY_SEPARATOR . $path);
    }

    if (!$paths[$k] || !is_readable($paths[$k])) {
      die(sprintf('ERROR Unable to read a "%s" path to scan.', $path));
    }
  }

  $options['paths'] = $paths;

  $exclude_file = $options['exclude-file'];
  if ($exclude_file) {
    if (strpos($exclude_file, './') !== 0 && strpos($exclude_file, '/') !== 0) {
      $exclude_file = getcwd() . DIRECTORY_SEPARATOR . $exclude_file;
    }
    if (!is_readable($exclude_file)) {
      die('ERROR Unable to read an exclude file.');
    }
    $options['exclude-file'] = $exclude_file;
  }

  if ($options['markdown'] !== FALSE) {
    // Table or a contents of the file with a template.
    $options['markdown'] = $options['markdown'] == 'table' ? 'table' : (is_readable($options['markdown']) ? file_get_contents($options['markdown']) : FALSE);
  }

  if ($options['ticks-list'] !== FALSE) {
    // A comma-separated list of strings or a file with additional "code" items.
    $options['ticks-list'] = is_readable($options['ticks-list'])
      ? array_filter(explode("\n", file_get_contents($options['ticks-list'])))
      : array_filter(explode(',', $options['ticks-list']));
  }

  set_config('debug', $options['debug']);
  set_config('markdown', $options['markdown']);
  set_config('exclude_file', $options['exclude-file']);
  set_config('csv_delim', $options['csv-delim']);
  set_config('ticks', $options['ticks']);
  set_config('ticks_list', $options['ticks-list']);
  set_config('slugify', $options['slugify']);
  set_config('unset_value', $options['unset']);
  set_config('filter_prefix', $options['filter-prefix']);
  set_config('filter_global', $options['filter-global']);
  set_config('paths', $options['paths']);
}

function get_targets($paths) {
  $files = [];

  foreach ($paths as $path) {
    if (is_file($path)) {
      $files[] = $path;
    }
    else {
      if (is_readable($path . '/.env')) {
        $files[] = $path . '/.env';
      }
      $files = array_merge($files, glob($path . '/*.{bash,sh}', GLOB_BRACE));
    }
  }

  return $files;
}

function extract_variables_from_file($file) {
  $content = file_get_contents($file);

  $lines = explode("\n", $content);

  $variables = [];
  foreach ($lines as $k => $line) {
    $variable_data = [
      'name' => '',
      'default_value' => '',
      'description' => '',
      'is_assignment' => FALSE,
    ];

    $variable_name_details = extract_variable_name($line);

    if (empty($variable_name_details)) {
      continue;
    }

    // Only use the very first occurrence.
    if (!empty($variables[$variable_name_details['name']])) {
      continue;
    }

    $variable_name = $variable_name_details['name'];

    if ($variable_name) {
      $variable_data['name'] = $variable_name;
      $variable_data['is_assignment'] = $variable_name_details['is_assignment'];

      if ($variable_name_details['is_assignment']) {
        $variable_value = extract_variable_value($line);
        if ($variable_value) {
          $variable_data['default_value'] = $variable_value;
        }
      }

      $variable_desc = extract_variable_description($lines, $k);
      if ($variable_desc) {
        $variable_data['description'] = $variable_desc;
      }

      $variables[$variable_data['name']] = $variable_data;
    }
  }

  return $variables;
}

function extract_variable_name($string) {
  $string = trim($string);

  if (!is_comment($string)) {
    // Assignment.
    if (preg_match('/^([a-zA-Z][a-zA-Z0-9_]*)=.*$/', $string, $matches)) {
      return [
        'name' => $matches[1],
        'is_assignment' => TRUE,
      ];
    }

    // Usage as ${variable}.
    if (preg_match('/\${([a-zA-Z][a-zA-Z0-9_]*)}/', $string, $matches)) {
      return [
        'name' => $matches[1],
        'is_assignment' => FALSE,
      ];
    }

    // Usage as $variable.
    if (preg_match('/\$([a-zA-Z][a-zA-Z0-9_]*)/', $string, $matches)) {
      return [
        'name' => $matches[1],
        'is_assignment' => FALSE,
      ];
    }
  }

  return FALSE;
}

function extract_variable_value($string) {
  $value = get_config('unset_value');

  $value_string = '';
  // Assignment.
  if (preg_match('/{?[a-zA-Z][a-zA-Z0-9_]*}?="?([^"]*)"?/', $string, $matches)) {
    $value_string = $matches[1];
  }

  if (empty($value_string)) {
    return $value;
  }

  // Value is in the second part of the assigned value.
  if (strpos($value_string, ':') !== FALSE) {
    if (preg_match('/\${[a-zA-Z][a-zA-Z0-9_]*:-?\$?{?([a-zA-Z][^}]*)/', $value_string, $matches)) {
      $value = $matches[1];
    }
  }
  else {
    // Value is a simple scalar or another value.
    if (preg_match('/{?([a-zA-Z][^}]*)/', $value_string, $matches)) {
      $value = $matches[1];
    }
    else {
      $value = $value_string;
    }
  }

  return $value;
}

function extract_variable_description($lines, $k, $comment_delim = '#') {
  $comment_lines = [];

  // Look up until the first non-comment line.
  while ($k > 0 && strpos(trim($lines[$k - 1]), $comment_delim) === 0) {
    $line = trim(ltrim(trim($lines[$k - 1]), $comment_delim));
    // Completely skip special comment lines.
    if (strpos(trim($lines[$k - 1]), '#;<') !== 0 && strpos(trim($lines[$k - 1]), '#;>') !== 0) {
      $comment_lines[] = $line;
    }
    $k--;
  }

  $comment_lines = array_reverse($comment_lines);
  array_walk($comment_lines, function (&$value) {
    $value = empty($value) ? "\n" : trim($value);
  });

  $output = implode(' ', $comment_lines);
  $output = str_replace(" \n ", "\n", $output);
  $output = str_replace(" \n", "\n", $output);
  $output = str_replace("\n ", "\n", $output);

  return $output;
}

function is_comment($string) {
  return strpos(trim($string), '#') === 0;
}

function render_variables_data($variables) {
  $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');

  fputcsv($csv, ['Name', 'Default value', 'Description'], get_config('csv_delim'));
  foreach ($variables as $variable) {
    if (get_config('ticks')) {
      $variable['name'] = '`' . $variable['name'] . '`';
      if (!empty($variable['default_value'])) {
        $variable['default_value'] = '`' . $variable['default_value'] . '`';
      }
    }

    fputcsv($csv, $variable, get_config('csv_delim'));
  }

  rewind($csv);

  return stream_get_contents($csv);
}

function process_description_ticks($variables) {
  $variables_sorted = $variables;
  krsort($variables_sorted, SORT_NATURAL);

  foreach ($variables as $k => $variable) {
    // Replace in description.
    $replaced = [];
    foreach (array_keys($variables_sorted) as $other_name) {
      // Cleanup and optionally convert variables to links.
      if (strpos($variable['description'], $other_name) !== FALSE) {
        $already_added = (bool) count(array_filter($replaced, function ($v) use ($other_name) {
          return strpos($v, $other_name) !== FALSE;
        }));

        if (!$already_added) {
          if (get_config('slugify')) {
            $other_name_slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $other_name));
            $replacement = sprintf('[`$%s`](#%s)', $other_name, $other_name_slug);
          }
          else {
            $replacement = '`$' . $other_name . '`';
          }
          $variable['description'] = preg_replace('/`?\$?' . $other_name . '`?/', $replacement, $variable['description']);
          $replaced[] = $other_name;
        }
      }
    }

    // Convert digits to code values.
    $variable['description'] = preg_replace('/\b((?<!`)[0-9]+)\b/', '`${1}`', $variable['description']);

    // Process all additional code items.
    if (get_config('ticks_list')) {
      foreach (get_config('ticks_list') as $token) {
        $token = trim($token);
        $variable['description'] = preg_replace('/\b((?<!`)' . preg_quote($token, '/') . ')\b/', '`${1}`', $variable['description']);
      }
    }

    $variables[$k] = $variable;
  }

  return $variables;
}

/**
 * Get configuration.
 */
function get_config($name, $default = NULL) {
  global $_config;

  return $_config[$name] ?? $default;
}

function set_config($name, $value) {
  global $_config;

  if (!is_null($value)) {
    $_config[$name] = $value;
  }
}

function get_configs() {
  global $_config;

  return $_config;
}

// ////////////////////////////////////////////////////////////////////////// //
//                                CSVTable                                    //
// ////////////////////////////////////////////////////////////////////////// //

/**
 * CSVTable.
 *
 * Credits: https://github.com/mre/CSVTable.
 */
class CSVTable {

  public function __construct($csv, $delim = ',', $enclosure = '"', $table_separator = '|') {
    $this->csv = $csv;
    $this->delim = $delim;
    $this->enclosure = $enclosure;
    $this->table_separator = $table_separator;

    // Fill the rows with Markdown output.
    // Table header.
    $this->header = "";
    // Table rows.
    $this->rows = "";
    $this->csvToTable();
  }

  private function csvToTable() {
    $parsed_array = $this->toArray($this->csv);
    $this->length = $this->minRowLength($parsed_array);
    $this->col_widths = $this->maxColumnWidths($parsed_array);

    $header_array = array_shift($parsed_array);
    $this->header = $this->createHeader($header_array);
    $this->rows = $this->createRows($parsed_array);
  }

  /**
   * Convert the CSV into a PHP array.
   */
  public function toArray($csv) {
    // Parse the rows.
    $parsed = str_getcsv($csv, "\n");
    $output = [];
    foreach ($parsed as &$row) {
      // Parse the items in rows.
      $row = str_getcsv($row, $this->delim, $this->enclosure);
      array_push($output, $row);
    }

    return $output;
  }

  private function createHeader($header_array) {
    return $this->createRow($header_array) . $this->createSeparator();
  }

  private function createSeparator() {
    $output = "";
    for ($i = 0; $i < $this->length - 1; ++$i) {
      $output .= str_repeat("-", $this->col_widths[$i]);
      $output .= $this->table_separator;
    }
    $last_index = $this->length - 1;
    $output .= str_repeat("-", $this->col_widths[$last_index]);

    return $output . "\n";
  }

  protected function createRows($rows) {
    $output = "";
    foreach ($rows as $row) {
      $output .= $this->createRow($row);
    }

    return $output;
  }

  /**
   * Add padding to a string.
   */
  private function padded($str, $width) {
    if ($width < strlen($str)) {
      return $str;
    }
    $padding_length = $width - strlen($str);
    $padding = str_repeat(" ", $padding_length);

    return $str . $padding;
  }

  protected function createRow($row) {
    $output = "";
    // Only create as many columns as the minimal number of elements
    // in all rows. Otherwise this would not be a valid Markdown table.
    for ($i = 0; $i < $this->length - 1; ++$i) {
      $element = $this->padded($row[$i], $this->col_widths[$i]);
      $output .= $element;
      $output .= $this->table_separator;
    }
    // Don't append a separator to the last element.
    $last_index = $this->length - 1;
    $element = $this->padded($row[$last_index], $this->col_widths[$last_index]);
    $output .= $element;
    // Row ends with a newline.
    $output .= "\n";

    return $output;
  }

  private function minRowLength($arr) {
    $min = PHP_INT_MAX;
    foreach ($arr as $row) {
      $row_length = count($row);
      if ($row_length < $min) {
        $min = $row_length;
      }
    }

    return $min;
  }

  /**
   * Calculate the maximum width of each column in characters.
   */
  private function maxColumnWidths($arr) {
    // Set all column widths to zero.
    $column_widths = array_fill(0, $this->length, 0);
    foreach ($arr as $row) {
      foreach ($row as $k => $v) {
        if ($column_widths[$k] < strlen($v)) {
          $column_widths[$k] = strlen($v);
        }
        if ($k == $this->length - 1) {
          // We don't need to look any further since these elements
          // will be dropped anyway because all table rows must have the
          // same length to create a valid Markdown table.
          break;
        }
      }
    }

    return $column_widths;
  }

  public function getMarkup() {
    return $this->header . $this->rows;
  }

}

/**
 * CSVBlock.
 */
class MarkdownBlocks {

  /**
   * Array of variables.
   *
   * @var array
   */
  protected $variables;

  /**
   * Template.
   *
   * @var string
   */
  protected $template;

  /**
   * Processed markup.
   *
   * @var string
   */
  protected $markup;

  public function __construct(array $variables, $template) {
    $this->variables = $variables;
    $this->template = $template;
    $this->markup = $this->csvToBlock();
  }

  protected function csvToBlock() {
    $content = '';

    foreach ($this->variables as $item) {
      $placeholders_tokens = array_map(function ($v) {
        return '{{ ' . $v . ' }}';
      }, array_keys($item));

      $placeholders_values = array_map(function ($v) {
        return str_replace("\n", "<br/>", $v);
      }, $item);

      $placeholders = array_combine($placeholders_tokens, $placeholders_values);
      $content .= str_replace("\n\n\n", "\n", strtr($this->template, $placeholders));
    }

    return $content;
  }

  public function toArray($csv) {
    $array = [];
    // Parse the rows.
    $parsed = str_getcsv($csv, "\n");
    foreach ($parsed as &$row) {
      // Parse the items in rows.
      $row = str_getcsv($row, $this->delim, $this->enclosure);
      array_push($array, $row);
    }

    return $array;
  }

  public function getMarkup() {
    return $this->markup;
  }

}

/**
 * Print help.
 */
function print_help() {
  $script_name = basename(__FILE__);
  print <<<EOF
Extract variables from shell scripts.
-------------------------------------

Arguments:
  file or directory     File or directory to scan. Multiple files seprated 
                        by space.

Options:
  --csv-delim|-c              
  --debug|-d            Debug script.
  --exclude-file|-e     Path to a file with exluded variables.
  --filter-global|-g  
  --filter-prefix|-p
  --help                This help.
  --markdown|-m         Output as markdown.
  --slugify|-s
  --ticks|-t
  --unset|-u            The string value of the variables without a value.

Examples:
  php $script_name path/to/file1 path/to/file2
   
  # With excluded variables specified in the file:
  ./$script_name -e ../excluded.txt path/to/file
 
  # Full:
  ./$script_name  -t -m -e ./excluded.txt -u "<NOT SET>" ../  

EOF;
  print PHP_EOL;
}

// ////////////////////////////////////////////////////////////////////////// //
//                                UTILITIES                                   //
// ////////////////////////////////////////////////////////////////////////// //

/**
 * Show a verbose message.
 */
function verbose() {
  if (getenv('SCRIPT_QUIET') != '1') {
    print call_user_func_array('sprintf', func_get_args()) . PHP_EOL;
  }
}

// ////////////////////////////////////////////////////////////////////////// //
//                                ENTRYPOINT                                  //
// ////////////////////////////////////////////////////////////////////////// //

ini_set('display_errors', 1);

if (PHP_SAPI != 'cli' || !empty($_SERVER['REMOTE_ADDR'])) {
  die('This script can be only ran from the command line.');
}

// Allow to skip the script run.
if (getenv('SCRIPT_RUN_SKIP') != 1) {
  // Custom error handler to catch errors based on set ERROR_LEVEL.
  set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
      // This error code is not included in error_reporting.
      return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
  });

  try {
    $code = main($argv, $argc);
    if (is_null($code)) {
      throw new \Exception('Script exited without providing an exit code.');
    }
    exit($code);
  }
  catch (\ErrorException $exception) {
    if ($exception->getSeverity() <= ERROR_LEVEL) {
      print PHP_EOL . 'RUNTIME ERROR: ' . $exception->getMessage() . PHP_EOL;
      exit($exception->getCode() == 0 ? EXIT_ERROR : $exception->getCode());
    }
  }
  catch (\Exception $exception) {
    print PHP_EOL . 'ERROR: ' . $exception->getMessage() . PHP_EOL;
    exit($exception->getCode() == 0 ? EXIT_ERROR : $exception->getCode());
  }
}

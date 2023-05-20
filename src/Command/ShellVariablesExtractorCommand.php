<?php

namespace AlexSkrypnyk\App\Command;

use AlexSkrypnyk\CsvTable\CsvTable;
use AlexSkrypnyk\CsvTable\Markdown;
use AlexSkrypnyk\App\MarkdownBlocks;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

/**
 * Class ShellVariablesExtractorCommand.
 *
 * Extracts variables from shell scripts.
 *
 * @package AlexSkrypnyk\App\Command
 */
class ShellVariablesExtractorCommand {

  /**
   * Defines exit codes.
   */
  const EXIT_SUCCESS = 0;

  /**
   * Array of configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function configure(SingleCommandApplication $command): void {
    $command
      ->setName('extract')
      ->setDescription('Extract variables from shell scripts.')
      ->setHelp(<<<EOD
        php \$script_name path/to/file1 path/to/file2
         
        # With excluded variables specified in the file:
        php \$script_name --exclude-file=../excluded.txt path/to/file
       
        # With excluded variables specified in the file, custom value for variables
        # without a value, and output as markdown with variables wrapped in ticks:
        php \$script_name --ticks --markdown --exclude-file=./excluded.txt --unset="<NOT SET>" ../        
      EOD
      );

    $command->addArgument(
      name: 'paths',
      mode: InputArgument::IS_ARRAY | InputArgument::REQUIRED,
      description: 'File or directory to scan. Multiple files separated by space.'
    );

    $command->addOption(
      name: 'csv-delim',
      shortcut: 'c',
      mode: InputOption::VALUE_REQUIRED,
      description: 'CSV delimiter character.',
      default: ';'
    );

    $command->addOption(
      name: 'debug',
      shortcut: 'd',
      mode: InputOption::VALUE_NONE,
      description: 'Enable debug mode.'
    );

    $command->addOption(
      name: 'exclude-file',
      shortcut: 'e',
      mode: InputOption::VALUE_REQUIRED,
      description: 'Path to a file with excluded variables.'
    );

    $command->addOption(
      name: 'filter-global',
      shortcut: 'g',
      mode: InputOption::VALUE_NONE,
      description: 'Exclude non-global variables.'
    );

    $command->addOption(
      name: 'filter-prefix',
      shortcut: 'p',
      mode: InputOption::VALUE_REQUIRED,
      description: 'Exclude variables with specified prefix.'
    );

    $command->addOption(
      name: 'markdown',
      shortcut: 'm',
      mode: InputOption::VALUE_REQUIRED,
      description: 'Output as markdown. Can be \'table\' or a path to a file with a custom template.',
      default: ''
    );

    $command->addOption(
      name: 'slugify',
      shortcut: 's',
      mode: InputOption::VALUE_NONE,
      description: 'Generate anchor links for variables in markdown output.'
    );

    $command->addOption(
      name: 'ticks',
      shortcut: 't',
      mode: InputOption::VALUE_NONE,
      description: 'Enclose variables and default values in backticks.'
    );

    $command->addOption(
      name: 'ticks-list',
      shortcut: 'l',
      mode: InputOption::VALUE_REQUIRED,
      description: 'File path for extra items to include in ticks.',
      default: ''
    );

    $command->addOption(
      name: 'unset',
      shortcut: 'u',
      mode: InputOption::VALUE_REQUIRED,
      description: 'The string value of the variables without a value.',
      default: '<UNSET>'
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $this->initCliArgsAndOptions($input);

    $files = $this->getTargets($input->getArgument('paths'));

    if ($this->getConfig('debug')) {
      print "Scanning files:\n" . implode("\n", $files) . "\n";
    }

    $all_variables = [];
    foreach ($files as $file) {
      $all_variables += $this->extractVariablesFromFile($file);
    }

    // Exclude local variables, if set.
    if ($this->getConfig('filter-global')) {
      $all_variables = array_filter($all_variables, function ($value) {
        return preg_match('/^[A-Z0-9_]+$/', $value['name']);
      });
    }

    $exclude_file = $this->getConfig('exclude-file');
    if ($exclude_file) {
      $excluded_variables = array_filter(explode("\n", file_get_contents($exclude_file)));
      $all_variables = array_diff_key($all_variables, array_flip($excluded_variables));
    }

    $filter_prefix = $this->getConfig('filter-prefix');
    if ($filter_prefix) {
      $all_variables = array_filter($all_variables, function ($value) use ($filter_prefix) {
        return !str_starts_with($value['name'], $filter_prefix);
      });
    }

    ksort($all_variables);

    if ($this->getConfig('ticks')) {
      $all_variables = $this->processDescriptionTicks($all_variables);
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

    // Make sure that there are always 3 elements in the array.
    array_walk($all_variables, function (&$value) {
      $value += [
        'name' => '',
        'default_value' => '',
        'description' => '',
      ];
    });

    if ($this->getConfig('markdown') == 'table') {
      $csv = $this->toCsv($all_variables);
      print (new CsvTable($csv, $this->getConfig('csv-delim')))->render(Markdown::class);
    }
    elseif ($this->getConfig('markdown')) {
      print (new MarkdownBlocks($all_variables, $this->getConfig('markdown')))->render();
    }
    else {
      print $this->toCsv($all_variables);
    }

    return self::EXIT_SUCCESS;
  }

  /**
   * Initialise CLI options.
   */
  public function initCliArgsAndOptions(InputInterface $input) {
    $defaults = [
      'csv-delim' => ';',
      'debug' => FALSE,
      'exclude-file' => NULL,
      'filter-global' => '',
      'filter-prefix' => '',
      'markdown' => FALSE,
      'slugify' => FALSE,
      'ticks' => FALSE,
      'ticks-list' => '',
      'unset' => '<UNSET>',
    ];

    foreach ($defaults as $name => $value) {
      $this->setConfig($name, $input->hasOption($name) ? $input->getOption($name) : $value);
    }

    $exclude_file = $this->getConfig('exclude-file');
    if ($exclude_file) {
      if (strpos($exclude_file, './') !== 0 && strpos($exclude_file, '/') !== 0) {
        $exclude_file = getcwd() . DIRECTORY_SEPARATOR . $exclude_file;
      }
      if (!is_readable($exclude_file)) {
        die('ERROR Unable to read an exclude file.');
      }
      $this->setConfig('exclude-file', $exclude_file);
    }

    if ($this->getConfig('markdown') !== FALSE) {
      $markdown = $this->getConfig('markdown');
      // Table or a contents of the file with a template.
      $markdown = $markdown == 'table' ? 'table' : (is_readable($markdown) ? file_get_contents($markdown) : FALSE);
      $this->setConfig('markdown', $markdown);
    }

    if ($this->getConfig('ticks-list') !== FALSE) {
      $tick_list = $this->getConfig('ticks-list');
      // A comma-separated list of strings or a file with additional "code"
      // items.
      $tick_list = is_readable($tick_list)
        ? array_filter(explode("\n", file_get_contents($tick_list)))
        : array_filter(explode(',', $tick_list));
      $this->setConfig('ticks-list', $tick_list);
    }
  }

  /**
   * Get a list of files to scan.
   *
   * @param array $paths
   *   A list of paths to scan.
   *
   * @return array
   *   A list of files to scan.
   */
  public function getTargets($paths) {
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

  /**
   * Extract variables from file.
   *
   * @param string $file
   *   Path to file.
   */
  public function extractVariablesFromFile($file) {
    $content = file_get_contents($file);

    $lines = explode("\n", $content);

    $variables = [];
    foreach ($lines as $num => $line) {
      $variable_data = [
        'name' => '',
        'default_value' => '',
        'description' => '',
        'is_assignment' => FALSE,
      ];

      $variable_name_details = $this->extractVariableName($line);

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
          $variable_value = $this->extractVariableValue($line);
          if ($variable_value) {
            $variable_data['default_value'] = $variable_value;
          }
        }

        $variable_desc = $this->extractVariableDescription($lines, $num);
        if ($variable_desc) {
          $variable_data['description'] = $variable_desc;
        }

        $variables[$variable_data['name']] = $variable_data;
      }
    }

    return $variables;
  }

  /**
   * Extract variable name from a line.
   *
   * @param string $string
   *   A line to extract a variable name from.
   *
   * @return array
   *   An array with a variable name and a flag if it's an assignment.
   */
  public function extractVariableName($string) {
    $string = trim($string);

    if (!$this->isComment($string)) {
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

  /**
   * Extract variable value from a line.
   *
   * @param string $string
   *   A line to extract a variable value from.
   *
   * @return string
   *   A variable value.
   */
  public function extractVariableValue($string) {
    $value = $this->getConfig('unset');

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

  /**
   * Extract variable description from a line.
   *
   * @param array $lines
   *   A list of lines to extract a variable description from.
   * @param int $line_num
   *   A line number to start from.
   * @param string $comment_delim
   *   A comment delimiter.
   *
   * @return string
   *   A variable description.
   */
  public function extractVariableDescription($lines, $line_num, $comment_delim = '#') {
    $comment_lines = [];

    // Look up until the first non-comment line.
    while ($line_num > 0 && strpos(trim($lines[$line_num - 1]), $comment_delim) === 0) {
      $line = trim(ltrim(trim($lines[$line_num - 1]), $comment_delim));
      // Completely skip special comment lines.
      if (strpos(trim($lines[$line_num - 1]), '#;<') !== 0 && strpos(trim($lines[$line_num - 1]), '#;>') !== 0) {
        $comment_lines[] = $line;
      }
      $line_num--;
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

  /**
   * Check if a string is a comment.
   *
   * @param string $string
   *   A string to check.
   *
   * @return bool
   *   TRUE if a string is a comment, FALSE otherwise.
   */
  public function isComment($string) {
    return strpos(trim($string), '#') === 0;
  }

  /**
   * Render variables data as CSV string.
   *
   * @param array $variables
   *   A list of variables to render.
   *
   * @return string
   *   A rendered CSV string.
   */
  public function toCsv($variables) {
    $csv = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');
    fputcsv($csv, ['Name', 'Default value', 'Description'], $this->getConfig('csv-delim'));
    foreach ($variables as $variable) {
      if ($this->getConfig('ticks')) {
        $variable['name'] = '`' . $variable['name'] . '`';
        if (!empty($variable['default_value'])) {
          $variable['default_value'] = '`' . $variable['default_value'] . '`';
        }
      }

      fputcsv($csv, $variable, $this->getConfig('csv-delim'));
    }

    rewind($csv);

    return stream_get_contents($csv);
  }

  /**
   * Process variable description ticks.
   *
   * @param array $variables
   *   A list of variables to process.
   *
   * @return array
   *   A list of processed variables.
   */
  public function processDescriptionTicks($variables) {
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
            if ($this->getConfig('slugify')) {
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
      if ($this->getConfig('ticks-list')) {
        foreach ($this->getConfig('ticks-list') as $token) {
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
  public function getConfig($name, $default = NULL) {
    return $this->config[$name] ?? $default;
  }

  /**
   * Set configuration.
   */
  public function setConfig($name, $value) {
    if (!is_null($value)) {
      $this->config[$name] = $value;
    }
  }

}

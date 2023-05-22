<?php

namespace AlexSkrypnyk\ShellVariablesExtractor;

use AlexSkrypnyk\ShellVariablesExtractor\Extractor\Extractor;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\FormatterFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

/**
 * Class Command.
 *
 * Extracts variables from shell scripts.
 */
class ShellVariablesExtractorCommand {

  /**
   * Command constructor.
   *
   * @param \Symfony\Component\Console\SingleCommandApplication $app
   *   The single command application instance.
   */
  public function __construct(SingleCommandApplication $app) {
    $app
      ->setName('shell-variables-extractor')
      ->setDescription('Extract variables from shell scripts.');

    $app->addArgument(
      name: 'paths',
      mode: InputArgument::IS_ARRAY | InputArgument::REQUIRED,
      description: 'File or directory to scan. Multiple files separated by space.'
    );

    $app->addOption(
      name: 'globals-only',
      mode: InputOption::VALUE_NONE,
      description: 'Indicates that the tool should only consider global variables, ignoring local variables.'
    );

    $app->addOption(
      name: 'exclude-prefix',
      mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
      description: 'Exclude variables that start with the provided prefix.'
    );

    $app->addOption(
      name: 'exclude-file',
      mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
      description: 'A path to a file that contains variables to be excluded from the extraction process.'
    );

    $app->addOption(
      name: 'unset',
      mode: InputOption::VALUE_REQUIRED,
      description: 'Specifies a placeholder value for variables that are defined but have no set value.',
      default: '<UNSET>'
    );

    $app->addOption(
      name: 'sort',
      mode: InputOption::VALUE_NONE,
      description: 'Sort variables by name.'
    );

    $app->addOption(
      name: 'format',
      mode: InputOption::VALUE_REQUIRED,
      description: 'The output format: a CSV file (csv), a markdown table (md-table), or markdown blocks (md-blocks).',
      default: 'csv'
    );

    $app->addOption(
      name: 'csv-separator',
      mode: InputOption::VALUE_REQUIRED,
      description: 'Separator for the CSV output format.',
      default: ';'
    );

    $app->addOption(
      name: 'md-link-vars',
      mode: InputOption::VALUE_NONE,
      description: 'Link variables within usages to their definitions in the Markdown output format.'
    );

    $app->addOption(
      name: 'md-inline-code-wrap-vars',
      mode: InputOption::VALUE_NONE,
      description: 'Wrap variables to show them as inline code in the Markdown output format.'
    );

    $app->addOption(
      name: 'md-inline-code-wrap-numbers',
      mode: InputOption::VALUE_NONE,
      description: 'Wrap numbers to show them as inline code in the Markdown output format.'
    );

    $app->addOption(
      name: 'md-inline-code-extra-file',
      mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
      description: 'A path to a file that contains additional strings to be formatted as inline code in the Markdown output format.',
      default: []
    );

    $app->addOption(
      name: 'md-block-template-file',
      mode: InputOption::VALUE_REQUIRED,
      description: "A path to a Markdown template file used for Markdown blocks (md-blocks) output format.\n{{ name }}, {{ description }} and {{ default_value }} tokens can be used within the template."
    );
  }

  /**
   * Execute the command.
   *
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The output.
   *
   * @return int
   *   The command exit code.
   */
  public function execute(InputInterface $input, OutputInterface $output): int {
    $targets = $this->scanTargets($input->getArgument('paths'));

    $config = $input->getOptions();
    $config['exclude-file'] = $this->resolvePaths($config['exclude-file']);
    $config['md-inline-code-extra-file'] = $this->resolvePaths($config['md-inline-code-extra-file']);
    $config['md-block-template-file'] = $this->resolvePath($config['md-block-template-file']);

    $variables = (new Extractor($targets, $config))->extract($targets);

    $formatted_output = FormatterFactory::create($config['format'], $variables, $config)->format();

    $output->write($formatted_output);

    return Command::SUCCESS;
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
  public function scanTargets($paths) {
    $files = [];

    $paths = $this->resolvePaths($paths);

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
   * Resolve paths.
   *
   * @param array $paths
   *   A list of paths to resolve.
   *
   * @return array
   *   A list of resolved paths.
   */
  protected function resolvePaths($paths) {
    $resolved_paths = [];

    foreach ($paths as $path) {
      $resolved_paths[] = $this->resolvePath($path);
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
  protected function resolvePath($path) {
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

<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Command;

use AlexSkrypnyk\ShellVariablesExtractor\Config\Config;
use AlexSkrypnyk\ShellVariablesExtractor\Extractor\ExtractorManager;
use AlexSkrypnyk\ShellVariablesExtractor\Filter\FilterManager;
use AlexSkrypnyk\ShellVariablesExtractor\Formatter\FormatterManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

/**
 * Class Command.
 *
 * Extracts variables from shell scripts.
 */
class VariablesExtractorCommand {

  /**
   * The config.
   *
   * @var \AlexSkrypnyk\ShellVariablesExtractor\Config\Config
   */
  protected $config;

  /**
   * Command constructor.
   *
   * @param \Symfony\Component\Console\SingleCommandApplication $app
   *   The single command application instance.
   */
  public function __construct(SingleCommandApplication $app) {
    $this->config = new Config();

    $app
      ->setName('shell-variables-extractor')
      ->setDescription('Extract variables from shell scripts.');

    $classes = [
      ExtractorManager::class,
      FilterManager::class,
      FormatterManager::class,
    ];

    /** @var \AlexSkrypnyk\ShellVariablesExtractor\Traits\SingletonInterface[] $classes */
    foreach ($classes as $class) {
      // @phpstan-ignore-next-line
      $instance = $class::getInstance($this->config);
      foreach ($instance->getAllConsoleArguments() as $argument) {
        if (!$app->getDefinition()->hasArgument($argument->getName())) {
          $app->getDefinition()->addArgument($argument);
        }
      }
      foreach ($instance->getAllConsoleOptions() as $option) {
        if (!$app->getDefinition()->hasOption($option->getName())) {
          $app->getDefinition()->addOption($option);
        }
      }
    }
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
    $this->config->setAll($input->getArguments(), $input->getOptions());

    // Extract.
    $variables = ExtractorManager::getInstance()->extract();

    // Filter.
    // Special case - reset instance to update config for filters.
    // @todo Fix this.
    FilterManager::resetInstance();
    $variables = FilterManager::getInstance($this->config)->filter($variables);

    // Output.
    $formatted_output = FormatterManager::getInstance()->format($variables);

    $output->write($formatted_output);

    return Command::SUCCESS;
  }

}

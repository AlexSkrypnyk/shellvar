<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Command;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Extractor\ExtractorManager;
use AlexSkrypnyk\Shellvar\Filter\FilterManager;
use AlexSkrypnyk\Shellvar\Formatter\FormatterManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExtractCommand.
 *
 * Extracts variables from shell scripts.
 */
class ExtractCommand extends Command {

  /**
   * The config.
   *
   * @var \AlexSkrypnyk\Shellvar\Config\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    $this
      ->setName('extract')
      ->setDescription('Extract variables from shell scripts.');

    $this->config = new Config();

    /** @var array<class-string<\AlexSkrypnyk\Shellvar\AbstractManager>> $classes */
    $classes = [
      ExtractorManager::class,
      FilterManager::class,
      FormatterManager::class,
    ];

    foreach ($classes as $class) {
      $instance = $class::getInstance($this->config);
      foreach ($instance->getAllConsoleArguments() as $argument) {
        if (!$this->getDefinition()->hasArgument($argument->getName())) {
          $this->getDefinition()->addArgument($argument);
        }
      }
      foreach ($instance->getAllConsoleOptions() as $option) {
        if (!$this->getDefinition()->hasOption($option->getName())) {
          $this->getDefinition()->addOption($option);
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
  protected function execute(InputInterface $input, OutputInterface $output): int {
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

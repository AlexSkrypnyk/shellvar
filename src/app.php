<?php

/**
 * @file
 * Main entry point for the application.
 */

use AlexSkrypnyk\ShellVariablesExtractor\VariablesExtractorCommand;
use Symfony\Component\Console\SingleCommandApplication;

$app = new SingleCommandApplication();
$app->setCode([new VariablesExtractorCommand($app), 'execute'])->run();

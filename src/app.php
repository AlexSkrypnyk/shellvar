<?php

/**
 * @file
 * Main entry point for the application.
 */

use AlexSkrypnyk\Shellvar\Command\ShellvarCommand;
use Symfony\Component\Console\SingleCommandApplication;

$app = new SingleCommandApplication();
$app->setCode([new ShellvarCommand($app), 'execute'])->run();

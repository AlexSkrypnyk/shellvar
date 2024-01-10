<?php

/**
 * @file
 * Main entry point for the application.
 */

use AlexSkrypnyk\Shellvar\Command\ExtractCommand;
use Symfony\Component\Console\Application;

// @codeCoverageIgnoreStart
$application = new Application();

$command = new ExtractCommand();
$application->add($command);

$application->run();
// @codeCoverageIgnoreEnd

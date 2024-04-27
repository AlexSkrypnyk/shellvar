<?php

/**
 * @file
 * Main entry point for the application.
 */

declare(strict_types=1);

use AlexSkrypnyk\Shellvar\Command\ExtractCommand;
use AlexSkrypnyk\Shellvar\Command\LintCommand;
use Symfony\Component\Console\Application;

// @codeCoverageIgnoreStart
$application = new Application();

$command = new ExtractCommand();
$application->add($command);

$command = new LintCommand();
$application->add($command);

$application->run();
// @codeCoverageIgnoreEnd

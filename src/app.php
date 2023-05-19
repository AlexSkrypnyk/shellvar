<?php

/**
 * @file
 * Entrypoint.
 */

use Drevops\App\Command\ShellVariablesExtractorCommand;
use Symfony\Component\Console\SingleCommandApplication;

$application = new SingleCommandApplication();
$command = new ShellVariablesExtractorCommand();
$command->configure($application);

$application->setCode([$command, 'execute'])->run();

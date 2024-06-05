<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Cli\Command;
use Cspray\AnnotatedContainer\Cli\CommandExecutor;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;

final class HelpCommand implements Command {

    public function __construct(
        private readonly CommandExecutor $commandExecutor
    ) {
    }

    public function name() : string {
        return 'help';
    }

    public function help() : string {
        $version = AnnotatedContainerVersion::version();
        return <<<SHELL
<bold>Annotated Container $version</bold>

Available Commands:

init

\tSetup your app to scan Composer directories, cache your ContainerDefinition, 
\tand generate an appropriate configuration file.

build

\tBuild your ContainerDefinition from the configuration file and cache it. 
\tBuilding a ContainerDefinition without configuring cache support will result 
\tin an error.

cache-clear

\tDestroy the cache to allow rebuilding the Container.
    
For more help:

help <command-name>
SHELL;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        $arguments = $input->arguments();
        $argc = count($arguments);
        if ($argc !== 2) {
            if ($argc > 1) {
                $output->stdout->write('<bg:red><fg:white>!! Warning !!</fg:white></bg:red> - Expecting 1 arg, showing default help');
                $output->stdout->br();
            }
            $output->stdout->write($this->help());
            return 0;
        }

        $commandName = $arguments[1];
        $command = $this->commandExecutor->command($commandName);

        if (!isset($command)) {
            $output->stderr->write(sprintf('<fg:red>Could not find command "%s"!</fg:red>', $commandName));
            return 1;
        }

        $output->stdout->write($command->help());
        return 0;
    }
}

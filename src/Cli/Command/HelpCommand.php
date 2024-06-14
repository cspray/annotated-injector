<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;

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
        $commands = '';
        foreach ($this->commandExecutor->commands() as $command) {
            $colorizedCommandName = sprintf(
                '<fg:%1$s>%2$s</fg:%1$s>',
                $command instanceof DisabledCommand ? 'red' : 'green',
                $command->name()
            );

            $commands .= sprintf(
                "%-33s%s%s",
                $colorizedCommandName,
                $command->summary(),
                PHP_EOL,
            );
        }

        return <<<SHELL
<bold>Annotated Container $version</bold>

This is a list of all available commands. For more information on a specific command please run "help <command-name>".
Commands listed in <fg:red>red</fg:red> are disabled and some action must be taken on your part to enable them.

$commands
SHELL;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        $arguments = $input->arguments();
        $argc = count($arguments);
        // there should be 2 arguments for the input, 'help <command-name>'
        // if <command-name> is not provided show the HelpCommand help
        if ($argc < 2) {
            $output->stdout->write($this->help());
            return 0;
        }

        $commandName = $arguments[1];
        $command = $this->commandExecutor->command($commandName);

        if (!isset($command)) {
            $output->stderr->write(sprintf('<bg:red><fg:white>Could not find command "%s"!</fg:white></bg:red>', $commandName));
            return 1;
        }

        $output->stdout->write($command->help());
        return 0;
    }

    public function summary() : string {
        return '';
    }
}

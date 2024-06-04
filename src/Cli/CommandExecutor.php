<?php

namespace Cspray\AnnotatedContainer\Cli;

use Throwable;

final class CommandExecutor {

    /**
     * @var Command[] $commands
     */
    private array $commands = [];

    private ?string $defaultCommand = null;

    public function defaultCommand(Command $command) : void {
        $this->defaultCommand = $command->name();
        $this->addCommand($command);
    }

    public function command(string $name) : ?Command {
        return array_reduce($this->commands, fn(?Command $carry, Command $item) => $item->name() === $name ? $item : $carry);
    }

    public function addCommand(Command $command) : void {
        $this->commands[$command->name()] = $command;
    }

    public function execute(Input $input, TerminalOutput $output) : int {
        $noArgs = empty($input->getArguments());
        $exitCode = null;
        if ($noArgs && !isset($this->defaultCommand)) {
            $this->notFoundCommand($output);
        } else {
            $command = $noArgs ? $this->defaultCommand : $input->getArguments()[0];
            if (!isset($this->commands[$command])) {
                $this->notFoundCommand($output, $command);
            } else {
                try {
                    $exitCode = $this->commands[$command]->handle($input, $output);
                } catch (Throwable $throwable) {
                    $exitCode = (int) $throwable->getCode();
                    // If using the default exception code then we need to make sure we don't return a success code
                    if ($exitCode === 0) {
                        $exitCode = 1;
                    }
                    $output->stderr->write(sprintf('Unhandled exception executing "%s"!', $command));
                    $output->stderr->br();
                    ;
                    $output->stderr->write(sprintf('Type: %s', $throwable::class));
                    $output->stderr->write(sprintf('Message: %s', $throwable->getMessage()));
                    $output->stderr->write(sprintf('Location: %sL#%s', $throwable->getFile(), $throwable->getLine()));
                    $output->stderr->write('Stack Trace:');
                    $output->stderr->br();
                    $output->stderr->write($throwable->getTraceAsString());
                }
            }
        }

        return $exitCode ?? 1;
    }

    private function notFoundCommand(TerminalOutput $output, string $command = null) : void {
        if (isset($command)) {
            $output->stderr->write(sprintf('<fg:red>Unable to find command "%s"!</fg:red>', $command));
        } else {
            $output->stderr->write('<fg:red>Unable to find command to execute!</fg:red>');
        }
        $output->stderr->br();
        $output->stderr->write('Available Commands:');
        $output->stderr->br();

        if (empty($this->commands)) {
            $output->stderr->write("\tNo Commands Found");
        } else {
            $names = array_keys($this->commands);
            sort($names);
            foreach ($names as $name) {
                $output->stderr->write(sprintf("\t%s", $name));
            }
        }
    }
}

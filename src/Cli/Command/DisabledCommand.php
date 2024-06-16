<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;

final class DisabledCommand implements Command {

    /**
     * @param non-empty-string $name
     * @param non-empty-string $howToEnable
     */
    public function __construct(
        private readonly string $name,
        private readonly string $howToEnable,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function name() : string {
        return $this->name;
    }

    public function summary() : string {
        return sprintf('Command is disabled. Run "help %s" to learn how to enable it.', $this->name());
    }

    public function help() : string {
        $command = $this->name();
        $howToEnable = $this->howToEnable;
        return <<<TEXT
To enable "$command":

$howToEnable

TEXT;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        $help = trim($this->help());
        $text = <<<TEXT
<bg:red><fg:white>Warning! This command is disabled!</fg:white></bg:red>

$help

TEXT;

        $output->stderr->write($text);

        return 1;
    }
}

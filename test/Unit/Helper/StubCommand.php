<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Closure;
use Cspray\AnnotatedContainer\Cli\Command;
use Cspray\AnnotatedContainer\Cli\Input;
use Cspray\AnnotatedContainer\Cli\TerminalOutput;

final class StubCommand implements Command {

    public function __construct(
        private readonly string $name,
        private readonly Closure $callable
    ) {
    }

    public function getName() : string {
        return $this->name;
    }

    public function handle(Input $input, TerminalOutput $output) : int {
        return (int) ($this->callable)($input, $output);
    }

    public function getHelp() : string {
        return 'Stub command help text';
    }
}

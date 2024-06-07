<?php

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;

interface Command {

    public function name() : string;

    public function summary() : string;

    public function help() : string;

    public function handle(Input $input, TerminalOutput $output) : int;
}

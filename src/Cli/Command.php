<?php

namespace Cspray\AnnotatedContainer\Cli;

interface Command {

    public function name() : string;

    public function help() : string;

    public function handle(Input $input, TerminalOutput $output) : int;
}

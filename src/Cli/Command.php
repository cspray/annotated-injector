<?php

namespace Cspray\AnnotatedContainer\Cli;

interface Command {

    public function getName() : string;

    public function getHelp() : string;

    public function handle(Input $input, TerminalOutput $output) : int;
}

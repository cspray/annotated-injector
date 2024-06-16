<?php

namespace Cspray\AnnotatedContainer\Cli\Command;

use Cspray\AnnotatedContainer\Cli\Input\Input;
use Cspray\AnnotatedContainer\Cli\Output\TerminalOutput;

interface Command {

    /**
     * @return non-empty-string
     */
    public function name() : string;

    /**
     * @return non-empty-string
     */
    public function summary() : string;

    /**
     * @return non-empty-string
     */
    public function help() : string;

    public function handle(Input $input, TerminalOutput $output) : int;
}

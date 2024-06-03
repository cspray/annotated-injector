<?php

namespace Cspray\AnnotatedContainer\Cli;

use Stringable;

final class Stderr implements Output {

    private readonly Output $output;

    public function __construct() {
        $this->output = new ResourceOutput(STDERR);
    }

    public function write(string|Stringable $msg, bool $appendNewLine = true) : void {
        $this->output->write($msg, $appendNewLine);
    }
}

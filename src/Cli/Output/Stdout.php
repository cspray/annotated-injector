<?php

namespace Cspray\AnnotatedContainer\Cli\Output;

use Stringable;

final class Stdout implements Output {

    private readonly Output $output;

    public function __construct() {
        $this->output = new ResourceOutput(STDOUT);
    }

    public function write(string|Stringable $msg, bool $appendNewLine = true) : void {
        $this->output->write($msg, $appendNewLine);
    }
}

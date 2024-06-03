<?php

namespace Cspray\AnnotatedContainer\Cli;

use Stringable;

interface Output {

    public function write(string|Stringable $msg, bool $appendNewLine = true) : void;
}

<?php

namespace Cspray\AnnotatedContainer\Cli\Output;

use Stringable;

interface Output {

    public function write(string|Stringable $msg, bool $appendNewLine = true) : void;
}

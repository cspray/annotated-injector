<?php

namespace Cspray\AnnotatedContainer\Cli\Output;

use Stringable;

final class ResourceOutput implements Output {

    /**
     * @param resource $resource
     */
    public function __construct(private $resource) {
    }

    public function write(string|Stringable $msg, bool $appendNewLine = true) : void {
        if ($appendNewLine) {
            $msg .= PHP_EOL;
        }
        fwrite($this->resource, (string) $msg);
    }
}

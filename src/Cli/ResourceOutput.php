<?php

namespace Cspray\AnnotatedContainer\Cli;

use Stringable;

final class ResourceOutput implements Output {

    /**
     * @var resource
     */
    private $resource;

    /**
     * @param resource $resource
     */
    public function __construct($resource) {
        $this->resource = $resource;
    }

    public function write(string|Stringable $msg, bool $appendNewLine = true) : void {
        if ($appendNewLine) {
            $msg .= PHP_EOL;
        }
        fwrite($this->resource, (string) $msg);
    }
}

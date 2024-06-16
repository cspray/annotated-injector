<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Cli\Output\Output;
use Stringable;

final class InMemoryOutput implements Output {

    private array $contents = [];

    public function getContents() : array {
        return $this->contents;
    }

    public function getContentsAsString() : string {
        return implode('', $this->contents);
    }

    public function write(string|Stringable $msg, bool $appendNewLine = true) : void {
        $msg = (string) $msg;
        if ($appendNewLine) {
            $msg .= PHP_EOL;
        }

        $this->contents[] = $msg;
    }
}

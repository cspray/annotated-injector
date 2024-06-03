<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidLogFile extends Exception {

    public static function fromLogFileNotWritable(string $file) : self {
        $message = sprintf('Unable to write to log file "%s".', $file);
        return new self($message);
    }
}

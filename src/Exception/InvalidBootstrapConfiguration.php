<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidBootstrapConfiguration extends Exception {

    public static function fromFileMissing(string $file) : self {
        return new self(sprintf('Provided configuration file %s does not exist.', $file));
    }

    public static function fromFileDoesNotValidateSchema(string $file) : self {
        $message = sprintf('Configuration file %s does not validate against the appropriate schema.', $file);
        return new self($message);
    }
}

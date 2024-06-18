<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidAutowireParameter extends Exception {

    public static function fromParameterWithMissingName() : self {
        return new self('A parameter name must have a non-empty value.');
    }

    public static function fromParameterAlreadyAddedToSet(string $parameter) : self {
        $message = sprintf(
            'A parameter named "%s" has already been added to this set.',
            $parameter
        );
        return new self($message);
    }
}

<?php

namespace Cspray\AnnotatedContainer\Exception;

final class EnvironmentVarNotFound extends Exception {

    public static function fromMissingEnvironmentVariable(string $envVar) : self {
        $message = sprintf(
            'The key "%s" is not available in store "env".',
            $envVar
        );
        return new self($message);
    }
}

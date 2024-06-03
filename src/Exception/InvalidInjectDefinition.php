<?php

namespace Cspray\AnnotatedContainer\Exception;

use Throwable;

final class InvalidInjectDefinition extends Exception {

    public static function fromMissingMethodAndProperty() : self {
        return new self('A method or property to inject into MUST be provided before building an InjectDefinition.');
    }

    public static function fromMethodAndPropertySet() : self {
        return new self('A method and property MUST NOT be set together when building an InjectDefinition.');
    }

    public static function fromMissingValue() : self {
        return new self('A value MUST be provided when building an InjectDefinition.');
    }

    public static function fromValueNotSerializable(Throwable $throwable) : self {
        $message = 'An InjectDefinition with a value that cannot be serialized was provided.';
        return new self($message, previous: $throwable);
    }
}

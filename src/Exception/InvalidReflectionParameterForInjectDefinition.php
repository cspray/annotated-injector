<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidReflectionParameterForInjectDefinition extends Exception {

    public static function fromReflectionParameterHasNoDeclaringClass() : self {
        return new self('A ReflectionParameter used to create an InjectDefinition MUST contain a declaring class.');
    }
}

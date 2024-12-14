<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

final class MultipleInjectOnSameParameter extends Exception {

    public static function fromClassMethodParamHasMultipleInject(string $class, string $method, string $param) : self {
        return new self(sprintf(
            'Multiple InjectDefinitions were found for %s::%s($%s).',
            $class,
            $method,
            // Auryn container factory uses special parameter names to differentiate how to deal with different types
            // we don't want these characters to show up in our exception message
            ltrim($param, '+:'),
        ));
    }
}

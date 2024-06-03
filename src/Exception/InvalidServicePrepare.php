<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidServicePrepare extends Exception {

    public static function fromClassNotService(string $prepareClass, string $prepareMethod) : self {
        $message = sprintf(
            'Service preparation defined on %s::%s, but that class is not a service.',
            $prepareClass,
            $prepareMethod
        );
        return new self($message);
    }
}

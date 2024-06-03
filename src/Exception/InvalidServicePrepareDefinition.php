<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidServicePrepareDefinition extends Exception {

    public static function fromEmptyPrepareMethod() : self {
        return new self('A method for a ServicePrepareDefinition must not be blank.');
    }
}

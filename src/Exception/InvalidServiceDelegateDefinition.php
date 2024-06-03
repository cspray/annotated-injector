<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidServiceDelegateDefinition extends Exception {

    public static function fromEmptyDelegateMethod() : self {
        return new self('The delegate method for a ServiceDelegateDefinition must not be blank.');
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionProperty;

final class WrongTargetForServicePrepareAttribute extends Exception {

    public static function fromServicePrepareAttributeNotTargetingMethod(
        ReflectionClass|ReflectionProperty|ReflectionClassConstant|ReflectionParameter|ReflectionFunction $reflection
    ) : self {
        return new self(sprintf(
            'The AnnotatedTarget::targetReflection MUST return an instance of ReflectionMethod but %s was provided.',
            $reflection::class
        ));
    }
}

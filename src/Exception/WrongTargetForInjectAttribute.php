<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

final class WrongTargetForInjectAttribute extends Exception {

    public static function fromInjectAttributeNotTargetMethodParameter(
        ReflectionClass|ReflectionProperty|ReflectionClassConstant|ReflectionMethod|ReflectionFunction $reflection
    ) : self {
        return new self(sprintf(
            'The AnnotatedTarget::targetReflection MUST return an instance of %s but %s was provided.',
            ReflectionParameter::class,
            $reflection::class
        ));
    }
}

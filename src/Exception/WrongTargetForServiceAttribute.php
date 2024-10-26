<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Reflection;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

final class WrongTargetForServiceAttribute extends Exception {

    public static function fromServiceAttributeNotTargetingClass(ReflectionProperty|ReflectionClassConstant|ReflectionMethod|ReflectionParameter|ReflectionFunction $reflection) : self {
        return new self(sprintf(
            'The AnnotatedTarget::targetReflection MUST return an instance of ReflectionClass but %s was provided.',
            $reflection::class,
        ));
    }
}

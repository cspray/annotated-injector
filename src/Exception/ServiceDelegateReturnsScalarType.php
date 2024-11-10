<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Reflection\Type;

final class ServiceDelegateReturnsScalarType extends Exception {

    public static function fromServiceDelegateCreatesScalarType(Type $delegateType, string $method) : self {
        return new self(sprintf(
            'The ServiceDelegate %s::%s returns a scalar type. All ServiceDelegates MUST return an object type.',
            $delegateType->name(),
            $method,
        ));
    }
}

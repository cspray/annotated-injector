<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Reflection\Type;

final class ServiceDelegateReturnsUnionType extends Exception {

    public static function fromServiceDelegateReturnsUnionType(Type $delegateType, string $delegateMethod) : self {
        return new self(sprintf(
            'The ServiceDelegate %s::%s returns a union type. At this time union types are not supported.',
            $delegateType->name(),
            $delegateMethod,
        ));
    }
}

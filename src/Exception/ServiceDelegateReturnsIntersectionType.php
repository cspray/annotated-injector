<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Reflection\Type;

final class ServiceDelegateReturnsIntersectionType extends Exception {

    public static function fromServiceDelegateCreatesIntersectionType(Type $delegateType, string $delegateMethod) : self {
        return new self(sprintf(
            'The ServiceDelegate %s::%s returns an intersection type. At this time intersection types are not supported.',
            $delegateType->name(),
            $delegateMethod,
        ));
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Reflection\Type;

final class ServiceDelegateReturnsUnknownType extends Exception {

    public static function fromServiceDelegateHasNoReturnType(Type $delegateType, string $method) : self {
        return new self(sprintf(
            'The ServiceDelegate %s::%s does not have a return type. A ServiceDelegate MUST declare an object return type.',
            $delegateType->name(),
            $method,
        ));
    }
}

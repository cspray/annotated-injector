<?php

namespace Cspray\AnnotatedContainer\Exception;

final class InvalidServiceDelegate extends Exception {

    public static function factoryMethodDoesNotCreateService(string $service, string $delegateClass, string $delegateMethod) : self {
        $message = sprintf(
            'Service delegation defined on %s::%s declares a type, %s, that is not a service.',
            $delegateClass,
            $delegateMethod,
            $service
        );
        return new self($message);
    }

    public static function factoryMethodReturnsScalarType(string $delegateClass, string $delegateMethod) : self {
        $message = sprintf(
            'The #[ServiceDelegate] Attribute on %s::%s declares a scalar value as a service type.',
            $delegateClass,
            $delegateMethod
        );
        return new self($message);
    }

    public static function factoryMethodReturnsIntersectionType(string $delegateClass, string $delegateMethod) : self {
        $message = sprintf(
            'The #[ServiceDelegate] Attribute on %s::%s declares an unsupported intersection as a service type.',
            $delegateClass,
            $delegateMethod
        );
        return new self($message);
    }

    public static function factoryMethodReturnsUnionType(string $delegateClass, string $delegateMethod) : self {
        $message = sprintf(
            'The #[ServiceDelegate] Attribute on %s::%s declares an unsupported union as a service type.',
            $delegateClass,
            $delegateMethod
        );
        return new self($message);
    }

    public static function factoryMethodDoesNotDeclareService(string $delegateClass, string $delegateMethod) : self {
        $message = sprintf(
            'The #[ServiceDelegate] Attribute on %s::%s does not declare a service in the Attribute or as a return type of the method.',
            $delegateClass,
            $delegateMethod
        );
        return new self($message);
    }
}

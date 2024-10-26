<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;

final class ServiceDelegateAttributeRequired extends Exception {

    public static function fromNotServiceDelegateAttributeInAnnotatedTarget(string $providedClass) : self {
        return new self(sprintf(
            'The AnnotatedTarget::attributeInstance MUST return a type of %s but %s was provided.',
            ServiceDelegateAttribute::class,
            $providedClass,
        ));
    }
}

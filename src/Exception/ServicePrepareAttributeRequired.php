<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;

final class ServicePrepareAttributeRequired extends Exception {

    /**
     * @param class-string $providedAttributeClass
     * @return self
     */
    public static function fromNotServicePrepareAttributeInAnnotatedTarget(string $providedAttributeClass) : self {
        return new self(sprintf(
            'The AnnotatedTarget::attributeInstance MUST return a type of %s but %s was provided.',
            ServicePrepareAttribute::class,
            $providedAttributeClass,
        ));
    }
}

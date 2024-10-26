<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;

final class ServiceAttributeRequired extends Exception {

    /**
     * @param class-string $providedAttributeClass
     */
    public static function fromNotServiceAttributeProvidedInAnnotatedTarget(string $providedAttributeClass) : self {
        return new self(sprintf(
            'The AnnotatedTarget::attributeInstance MUST return a type of %s but %s was provided.',
            ServiceAttribute::class,
            $providedAttributeClass
        ));
    }
}

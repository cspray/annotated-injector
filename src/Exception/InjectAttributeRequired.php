<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;

final class InjectAttributeRequired extends Exception {

    public static function fromNotInjectAttributeProvidedInAnnotatedTarget(string $providedClass) : self {
        return new self(sprintf(
            'An AnnotatedTarget::attributeInstance() MUST return an instance of %s but %s was provided.',
            InjectAttribute::class,
            $providedClass,
        ));
    }
}

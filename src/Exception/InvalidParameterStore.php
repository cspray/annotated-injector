<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;

final class InvalidParameterStore extends Exception {

    public static function fromParameterStoreIdentifierNotClass(string $identifier) : self {
        return new self(sprintf(
            'Attempted to create a parameter store, "%s", that is not a class.',
            $identifier
        ));
    }

    public static function fromIdentifierNotParameterStore(string $identifier) : self {
        return new self(sprintf(
            'Attempted to create a parameter store, "%s", that is not a %s',
            $identifier,
            ParameterStore::class
        ));
    }
}

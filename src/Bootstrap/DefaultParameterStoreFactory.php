<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Exception\InvalidParameterStore;

final class DefaultParameterStoreFactory implements ParameterStoreFactory {

    /**
     * @param string|class-string<ParameterStore> $identifier
     * @return ParameterStore
     */
    public function createParameterStore(string $identifier) : ParameterStore {
        if (!class_exists($identifier)) {
            throw InvalidParameterStore::fromParameterStoreIdentifierNotClass($identifier);
        }

        if (!is_a($identifier, ParameterStore::class, true)) {
            throw InvalidParameterStore::fromIdentifierNotParameterStore($identifier);
        }

        return new $identifier();
    }
}

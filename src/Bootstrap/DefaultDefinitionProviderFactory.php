<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Exception\InvalidDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;

final class DefaultDefinitionProviderFactory implements DefinitionProviderFactory {

    public function createProvider(string $identifier) : DefinitionProvider {
        if (!class_exists($identifier)) {
            throw InvalidDefinitionProvider::fromDefinitionProviderIdentifierNotClass($identifier);
        }

        if (!is_a($identifier, DefinitionProvider::class, true)) {
            throw InvalidDefinitionProvider::fromIdentifierNotDefinitionProvider($identifier);
        }

        return new $identifier();
    }
}

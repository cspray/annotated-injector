<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;

final class InvalidDefinitionProvider extends Exception {
    public static function fromDefinitionProviderIdentifierNotClass(string $identifier) : self {
        return new self(sprintf(
            'Attempted to create a definition provider, "%s", that is not a class.',
            $identifier
        ));
    }

    public static function fromIdentifierNotDefinitionProvider(string $identifier) : self {
        return new self(sprintf(
            'Attempted to create a definition provider, "%s", that is not a %s',
            $identifier,
            DefinitionProvider::class
        ));
    }
}

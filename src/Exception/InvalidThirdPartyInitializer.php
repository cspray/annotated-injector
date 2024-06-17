<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializer;
use Cspray\AnnotatedContainer\Bootstrap\ThirdPartyInitializerProvider;

final class InvalidThirdPartyInitializer extends Exception {

    public static function fromComposerExtraAnnotatedContainerConfigNotArray() : self {
        return new self('The value listed in composer.json extra.$annotatedContainer MUST be an array.');
    }

    public static function fromComposerExtraAnnotatedContainerConfigNoInitializers() : self {
        return new self('The value listed in composer.json extra.$annotatedContainer MUST have a key "initializers" that holds a list of ThirdPartyInitializers to use.');
    }

    public static function fromComposerExtraAnnotatedContainerConfigInitializersNotArray() : self {
        return new self('The value listed in composer.json extra.$annotatedContainer.initializers MUST be a list of ThirdPartyInitializers to use.');
    }

    public static function fromConfiguredProviderNotClass(string $path, mixed $initializerProvider) : self {
        return new self(sprintf(
            'Values listed in %s extra.$annotatedContainer.initializers MUST be a class-string that is an instance of ' .
            '%s but the value "%s" is present.',
            $path,
            ThirdPartyInitializer::class,
            var_export($initializerProvider, true),
        ));
    }

    public static function fromConfiguredProviderNotThirdPartyInitializer(string $path, string $initializerProvider) : self {
        return new self(sprintf(
            'Values listed in %s extra.$annotatedContainer.initializers MUST be a class-string that is an instance of ' .
            '%s but a value that is an instance of "%s" is present.',
            $path,
            ThirdPartyInitializer::class,
            $initializerProvider,
        ));
    }
}

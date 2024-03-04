<?php

namespace Cspray\AnnotatedContainer\Exception;

use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalyticsObserver;
use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Bootstrap\PostAnalysisObserver;
use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;

final class InvalidBootstrapConfiguration extends Exception {

    public static function fromFileMissing(string $file) : self {
        return new self(sprintf('Provided configuration file %s does not exist.', $file));
    }

    public static function fromFileDoesNotValidateSchema(string $file) : self {
        $message = sprintf('Configuration file %s does not validate against the appropriate schema.', $file);
        return new self($message);
    }

    public static function fromConfiguredDefinitionProviderWrongType(string $class) : self {
        $message = sprintf(
            'The entry %s in definitionProviders does not implement the %s interface.',
            $class,
            DefinitionProvider::class
        );
        return new self($message);
    }

    public static function fromConfiguredParameterStoreWrongType(string $class) : self {
        $message = sprintf(
            'The entry %s in parameterStores does not implement the %s interface.',
            $class,
            ParameterStore::class
        );
        return new self($message);
    }

}

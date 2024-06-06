<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

/**
 * This interface provides a way to define a BootstrappingConfiguration ensuring that the same dependencies used by the
 * Bootstrap are made available, if needed.
 *
 * Specifically, it is expected that a BootstrappingConfiguration will require the use of a ParameterStoreFactory and
 * DefinitionProviderFactory to satisfy requirements and a BootstrappingDirectoryResolver might be required to determine
 * the appropriate path for a file-based configuration. If you are utilizing Bootstrap::fromMinimalSetup you would not
 * normally have access to these objects and this allows for that possibility.
 */
interface BootstrappingConfigurationProvider {

    public function bootstrappingConfiguration(
        BootstrappingDirectoryResolver $directoryResolver,
        ParameterStoreFactory $parameterStoreFactory,
        DefinitionProviderFactory $definitionProviderFactory,
    ) : BootstrappingConfiguration;
}

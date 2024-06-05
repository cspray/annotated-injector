<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

interface BootstrappingConfigurationProvider {

    public function bootstrappingConfiguration(
        BootstrappingDirectoryResolver $directoryResolver,
        ParameterStoreFactory $parameterStoreFactory,
        DefinitionProviderFactory $definitionProviderFactory,
    ) : BootstrappingConfiguration;
}

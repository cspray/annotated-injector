<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

final class XmlBootstrappingConfigurationProvider implements BootstrappingConfigurationProvider {

    public function __construct(
        private readonly string $fileName = 'annotated-container.xml'
    ) {
    }

    public function bootstrappingConfiguration(
        BootstrappingDirectoryResolver $directoryResolver,
        ParameterStoreFactory $parameterStoreFactory,
        DefinitionProviderFactory $definitionProviderFactory,
    ) : BootstrappingConfiguration {
        $configFile = $directoryResolver->configurationPath($this->fileName);
        return new XmlBootstrappingConfiguration(
            $configFile,
            $parameterStoreFactory,
            $definitionProviderFactory
        );
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;

final class CacheAwareBootstrappingConfigurationProvider implements BootstrappingConfigurationProvider {

    public function __construct(
        private readonly BootstrappingConfigurationProvider $configurationProvider,
        private readonly ContainerDefinitionCache $cache
    ) {
    }

    public function bootstrappingConfiguration(
        BootstrappingDirectoryResolver $directoryResolver,
        ParameterStoreFactory $parameterStoreFactory,
        DefinitionProviderFactory $definitionProviderFactory,
    ) : BootstrappingConfiguration {
        return new CacheAwareBootstrappingConfiguration(
            $this->configurationProvider->bootstrappingConfiguration($directoryResolver, $parameterStoreFactory, $definitionProviderFactory),
            $this->cache
        );
    }
}

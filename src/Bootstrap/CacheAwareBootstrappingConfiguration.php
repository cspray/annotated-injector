<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;

final class CacheAwareBootstrappingConfiguration implements BootstrappingConfiguration {

    public function __construct(
        private readonly BootstrappingConfiguration $configuration,
        private readonly ContainerDefinitionCache $cache,
    ) {
    }

    public function scanDirectories() : array {
        return $this->configuration->scanDirectories();
    }

    public function cache() : ?ContainerDefinitionCache {
        return $this->cache;
    }

    #[SingleEntrypointDefinitionProvider]
    public function containerDefinitionProvider() : ?DefinitionProvider {
        return $this->configuration->containerDefinitionProvider();
    }

    public function parameterStores() : array {
        return $this->configuration->parameterStores();
    }
}

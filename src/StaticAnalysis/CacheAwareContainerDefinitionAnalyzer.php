<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

/**
 * A ContainerDefinitionCompiler decorator that allows for a ContainerDefinition to be serialized and cached to the
 * filesystem; this could potentially save time on very large codebase or be used when building production to not
 * require Container compilation on every request.
 */
final class CacheAwareContainerDefinitionAnalyzer implements ContainerDefinitionAnalyzer {

    public function __construct(
        private readonly ContainerDefinitionAnalyzer $containerDefinitionAnalyzer,
        private readonly ContainerDefinitionCache    $containerDefinitionCache
    ) {
    }

    public function analyze(ContainerDefinitionAnalysisOptions $containerDefinitionAnalysisOptions): ContainerDefinition {
        $cacheKey = CacheKey::fromContainerDefinitionAnalysisOptions($containerDefinitionAnalysisOptions);
        $cachedDefinition = $this->containerDefinitionCache->get($cacheKey);
        if ($cachedDefinition === null) {
            $containerDefinition = $this->containerDefinitionAnalyzer->analyze($containerDefinitionAnalysisOptions);
            $this->containerDefinitionCache->set($cacheKey, $containerDefinition);
            return $containerDefinition;
        }
        return $cachedDefinition;
    }
}

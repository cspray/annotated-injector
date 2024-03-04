<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

interface AnalyzedContainerDefinitionFromCache {

    public function handleAnalyzedContainerDefinitionFromCache(ContainerDefinition $containerDefinition, string $cacheFile) : void;

}

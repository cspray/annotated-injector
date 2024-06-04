<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Listener;

interface AnalyzedContainerDefinitionFromCache extends Listener {

    public function handleAnalyzedContainerDefinitionFromCache(ContainerDefinition $containerDefinition, string $cacheFile) : void;
}

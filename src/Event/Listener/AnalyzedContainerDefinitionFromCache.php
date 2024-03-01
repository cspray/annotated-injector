<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

interface AnalyzedContainerDefinitionFromCache {

    public function handle(ContainerDefinition $containerDefinition, string $cacheFile) : void;

}

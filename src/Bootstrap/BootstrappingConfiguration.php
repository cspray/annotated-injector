<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;

interface BootstrappingConfiguration {

    /**
     * @return list<string>
     */
    public function scanDirectories() : array;

    public function cache() : ?ContainerDefinitionCache;

    #[SingleEntrypointDefinitionProvider]
    public function containerDefinitionProvider() : ?DefinitionProvider;

    /**
     * @return list<ParameterStore>
     */
    public function parameterStores() : array;
}

<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;

interface BootstrappingConfiguration {

    /**
     * @return list<string>
     */
    public function getScanDirectories() : array;

    public function getCacheDirectory() : ?string;

    #[SingleEntrypointDefinitionProvider]
    public function getContainerDefinitionProvider() : ?DefinitionProvider;

    /**
     * @return list<ParameterStore>
     */
    public function getParameterStores() : array;

}

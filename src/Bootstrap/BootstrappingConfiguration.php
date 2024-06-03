<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Psr\Log\LoggerInterface;

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

    /**
     * @return list<PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver|ContainerAnalyticsObserver>
     * @deprecated
     */
    public function getObservers() : array;

    /**
     * @return LoggerInterface|null
     * @deprecated
     */
    public function getLogger() : ?LoggerInterface;

    /**
     * @return list<string>
     * @deprecated
     */
    public function getLoggingExcludedProfiles() : array;
}

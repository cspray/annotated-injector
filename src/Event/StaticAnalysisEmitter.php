<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface StaticAnalysisEmitter {

    public function emitBeforeContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions) : void;

    public function emitAnalyzedServiceDefinitionFromAttribute(
        AnnotatedTarget $annotatedTarget,
        ServiceDefinition $serviceDefinition,
    ) : void;

    public function emitAnalyzedServicePrepareDefinitionFromAttribute(
        AnnotatedTarget $annotatedTarget,
        ServicePrepareDefinition $servicePrepareDefinition,
    ) : void;

    public function emitAnalyzedServiceDelegateDefinitionFromAttribute(
        AnnotatedTarget $annotatedTarget,
        ServiceDelegateDefinition $serviceDelegateDefinition,
    ) : void;

    public function emitAnalyzedInjectDefinitionFromAttribute(
        AnnotatedTarget $annotatedTarget,
        InjectDefinition $injectDefinition,
    ) : void;

    public function emitAddedInjectDefinitionFromApi(InjectDefinition $injectDefinition) : void;

    public function emitAddedServiceDefinitionFromApi(ServiceDefinition $serviceDefinition) : void;

    public function emitAddedServiceDelegateDefinitionFromApi(ServiceDelegateDefinition $serviceDelegateDefinition) : void;

    public function emitAddedServicePrepareDefinitionFromApi(ServicePrepareDefinition $servicePrepareDefinition) : void;

    public function emitAddedAliasDefinition(AliasDefinition $aliasDefinition) : void;

    public function emitAnalyzedContainerDefinitionFromCache(
        ContainerDefinition $definition,
        string $cacheFile
    ) : void;

    public function emitAfterContainerAnalysis(
        ContainerDefinitionAnalysisOptions $analysisOptions,
        ContainerDefinition $containerDefinition,
    ) : void;

}

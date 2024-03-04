<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedAliasDefinition;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AfterContainerAnalysis;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedContainerDefinitionFromCache;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedInjectDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServiceDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServiceDelegateDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServicePrepareDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\BeforeContainerAnalysis;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedTarget\AnnotatedTarget;

class StubAnalysisListener implements BeforeContainerAnalysis,
    AnalyzedServiceDefinitionFromAttribute,
    AnalyzedServiceDelegateDefinitionFromAttribute,
    AnalyzedServicePrepareDefinitionFromAttribute,
    AnalyzedInjectDefinitionFromAttribute,
    AnalyzedContainerDefinitionFromCache,
    AddedAliasDefinition,
    AfterContainerAnalysis {

    private readonly AnalysisEventCollection $triggeredEvents;

    public function __construct() {
        $this->triggeredEvents = new AnalysisEventCollection();
    }

    public function getTriggeredEvents() : AnalysisEventCollection {
        return $this->triggeredEvents;
    }

    public function handleAnalyzedContainerDefinitionFromCache(ContainerDefinition $containerDefinition, string $cacheFile) : void {
        $this->triggeredEvents->add(AnalysisEvent::AnalyzedContainerDefinitionFromCache);
    }

    public function handleAnalyzedInjectDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, InjectDefinition $injectDefinition) : void {
        $this->triggeredEvents->add(AnalysisEvent::AnalyzedInjectDefinitionFromAttribute);
    }

    public function handleAnalyzedServiceDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDefinition $serviceDefinition) : void {
        $this->triggeredEvents->add(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute);
    }

    public function handleAnalyzedServiceDelegateDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDelegateDefinition $definition) : void {
        $this->triggeredEvents->add(AnalysisEvent::AnalyzedServiceDelegateDefinitionFromAttribute);
    }

    public function handleAnalyzedServicePrepareDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServicePrepareDefinition $definition) : void {
        $this->triggeredEvents->add(AnalysisEvent::AnalyzedServicePrepareDefinitionFromAttribute);
    }

    public function handleAddedAliasDefinition(AliasDefinition $aliasDefinition) : void {
        $this->triggeredEvents->add(AnalysisEvent::AddedAliasDefinition);
    }

    public function handleBeforeContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions) : void {
        $this->triggeredEvents->add(AnalysisEvent::BeforeContainerAnalysis);
    }

    public function handleAfterContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions, ContainerDefinition $containerDefinition) : void {
        $this->triggeredEvents->add(AnalysisEvent::AfterContainerAnalysis);
    }

}

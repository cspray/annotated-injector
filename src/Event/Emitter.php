<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\Listener\AfterBootstrap;
use Cspray\AnnotatedContainer\Event\Listener\AfterContainerAnalysis;
use Cspray\AnnotatedContainer\Event\Listener\AfterContainerCreation;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedContainerDefinitionFromCache;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedInjectDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedServiceDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedServiceDelegateDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\AnalyzedServicePrepareDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\BeforeBootstrap;
use Cspray\AnnotatedContainer\Event\Listener\BeforeContainerAnalysis;
use Cspray\AnnotatedContainer\Event\Listener\BeforeContainerCreation;
use Cspray\AnnotatedContainer\Event\Listener\InjectingMethodParameter;
use Cspray\AnnotatedContainer\Event\Listener\InjectingProperty;
use Cspray\AnnotatedContainer\Event\Listener\ServiceAliasResolution;
use Cspray\AnnotatedContainer\Event\Listener\ServiceDelegated;
use Cspray\AnnotatedContainer\Event\Listener\ServiceFilteredDueToProfiles;
use Cspray\AnnotatedContainer\Event\Listener\ServicePrepared;
use Cspray\AnnotatedContainer\Event\Listener\ServiceShared;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedTarget\AnnotatedTarget;

/**
 * @psalm-type Listeners = BeforeBootstrap|BeforeContainerAnalysis
 */
final class Emitter implements AnalysisEmitter, BootstrapEmitter, ContainerFactoryEmitter {

    /**
     * @var list<BeforeBootstrap>
     */
    private array $beforeBootstraps = [];

    /**
     * @var list<BeforeContainerAnalysis>
     */
    private array $beforeContainerAnalysis = [];

    /**
     * @var list<AnalyzedServiceDefinitionFromAttribute>
     */
    private array $analyzedServiceDefinitionFromAttributes = [];

    /**
     * @var list<AnalyzedServicePrepareDefinitionFromAttribute>
     */
    private array $analyzedServicePrepareDefinitionFromAttributes = [];

    /**
     * @var list<AnalyzedServiceDelegateDefinitionFromAttribute>
     */
    private array $analyzedServiceDelegateDefinitionFromAttributes = [];

    /**
     * @var list<AnalyzedInjectDefinitionFromAttribute>
     */
    private array $analyzedInjectDefinitionFromAttributes = [];

    /**
     * @var list<AnalyzedContainerDefinitionFromCache>
     */
    private array $analyzedContainerDefinitionFromCaches = [];

    /**
     * @var list<AfterContainerAnalysis>
     */
    private array $afterContainerAnalysis = [];

    /**
     * @var list<BeforeContainerCreation>
     */
    private array $beforeContainerCreations = [];

    /**
     * @var list<ServiceFilteredDueToProfiles>
     */
    private array $serviceFilteredDueToProfiles = [];

    /**
     * @var list<ServiceShared>
     */
    private array $serviceShared = [];

    /**
     * @var list<InjectingMethodParameter>
     */
    private array $injectingMethodParameters = [];

    /**
     * @var list<InjectingProperty>
     */
    private array $injectingProperties = [];

    /**
     * @var list<ServicePrepared>
     */
    private array $servicePrepareds = [];

    /**
     * @var list<ServiceDelegated>
     */
    private array $serviceDelegateds = [];

    /**
     * @var list<ServiceAliasResolution>
     */
    private array $serviceAliasResolutions = [];

    /**
     * @var list<AfterContainerCreation>
     */
    private array $afterContainerCreation = [];

    /**
     * @var list<AfterBootstrap>
     */
    private array $afterBootstraps = [];

    public function addBeforeBootstrapListener(BeforeBootstrap $listener) : void {
        $this->beforeBootstraps[] = $listener;
    }

    public function addBeforeContainerAnalysisListener(BeforeContainerAnalysis $listener) : void {
        $this->beforeContainerAnalysis[] = $listener;
    }

    public function addAnalyzedServiceDefinitionFromAttributeListener(AnalyzedServiceDefinitionFromAttribute $listener) : void {
        $this->analyzedServiceDefinitionFromAttributes[] = $listener;
    }

    public function addAnalyzedServicePrepareDefinitionFromAttributeListener(AnalyzedServicePrepareDefinitionFromAttribute $listener) : void {
        $this->analyzedServicePrepareDefinitionFromAttributes[] = $listener;
    }

    public function addAnalyzedServiceDelegateDefinitionFromAttributeListener(AnalyzedServiceDelegateDefinitionFromAttribute $listener) : void {
        $this->analyzedServiceDelegateDefinitionFromAttributes[] = $listener;
    }

    public function addAnalyzedInjectDefinitionFromAttributeListener(AnalyzedInjectDefinitionFromAttribute $listener) : void {
        $this->analyzedInjectDefinitionFromAttributes[] = $listener;
    }

    public function addAnalyzedContainerDefinitionFromCacheListener(AnalyzedContainerDefinitionFromCache $listener) : void {
        $this->analyzedContainerDefinitionFromCaches[] = $listener;
    }

    public function addAfterContainerAnalysisListener(AfterContainerAnalysis $listener) : void {
        $this->afterContainerAnalysis[] = $listener;
    }

    public function addBeforeContainerCreationListener(BeforeContainerCreation $listener) : void {
        $this->beforeContainerCreations[] = $listener;
    }

    public function addServiceFilteredDueToProfilesListener(ServiceFilteredDueToProfiles $listener) : void {
        $this->serviceFilteredDueToProfiles[] = $listener;
    }

    public function addServiceSharedListener(ServiceShared $listener) : void {
        $this->serviceShared[] = $listener;
    }

    public function addServiceDelegatedListener(ServiceDelegated $listener) : void {
        $this->serviceDelegateds[] = $listener;
    }

    public function addServicePreparedListener(ServicePrepared $listener) : void {
        $this->servicePrepareds[] = $listener;
    }

    public function addInjectingMethodParameterListener(InjectingMethodParameter $listener) : void {
        $this->injectingMethodParameters[] = $listener;
    }

    public function addInjectingPropertyListener(InjectingProperty $listener) : void {
        $this->injectingProperties[] = $listener;
    }

    public function addServiceAliasResolutionListener(ServiceAliasResolution $listener) : void {
        $this->serviceAliasResolutions[] = $listener;
    }

    public function addAfterContainerCreationListener(AfterContainerCreation $listener) : void {
        $this->afterContainerCreation[] = $listener;
    }

    public function addAfterBootstrapListener(AfterBootstrap $listener) : void {
        $this->afterBootstraps[] = $listener;
    }

    public function emitBeforeContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions) : void {
        foreach ($this->beforeContainerAnalysis as $beforeContainerAnalysis) {
            $beforeContainerAnalysis->handleBeforeContainerAnalysis($analysisOptions);
        }
    }

    public function emitAnalyzedServiceDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDefinition $serviceDefinition,) : void {
        foreach ($this->analyzedServiceDefinitionFromAttributes as $analyzedServiceDefinitionFromAttribute) {
            $analyzedServiceDefinitionFromAttribute->handleAnalyzedServiceDefinitionFromAttribute($annotatedTarget, $serviceDefinition);
        }
    }

    public function emitAnalyzedServicePrepareDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServicePrepareDefinition $servicePrepareDefinition,) : void {
        foreach ($this->analyzedServicePrepareDefinitionFromAttributes as $analyzedServicePrepareDefinitionFromAttribute) {
            $analyzedServicePrepareDefinitionFromAttribute->handleAnalyzedServicePrepareDefinitionFromAttribute($annotatedTarget, $servicePrepareDefinition);
        }
    }

    public function emitAnalyzedServiceDelegateDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDelegateDefinition $serviceDelegateDefinition,) : void {
        foreach ($this->analyzedServiceDelegateDefinitionFromAttributes as $analyzedServiceDelegateDefinitionFromAttribute) {
            $analyzedServiceDelegateDefinitionFromAttribute->handleAnalyzedServiceDelegateDefinitionFromAttribute($annotatedTarget, $serviceDelegateDefinition);
        }
    }

    public function emitAnalyzedInjectDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, InjectDefinition $injectDefinition) : void {
        foreach ($this->analyzedInjectDefinitionFromAttributes as $analyzedInjectDefinitionFromAttribute) {
            $analyzedInjectDefinitionFromAttribute->handleAnalyzedInjectDefinitionFromAttribute($annotatedTarget, $injectDefinition);
        }
    }

    public function emitAnalyzedContainerDefinitionFromCache(ContainerDefinition $definition, string $cacheFile) : void {
        foreach ($this->analyzedContainerDefinitionFromCaches as $analyzedContainerDefinitionFromCache) {
            $analyzedContainerDefinitionFromCache->handleAnalyzedContainerDefinitionFromCache($definition, $cacheFile);
        }
    }

    public function emitAfterContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions, ContainerDefinition $containerDefinition,) : void {
        foreach ($this->afterContainerAnalysis as $afterContainerAnalysis) {
            $afterContainerAnalysis->handleAfterContainerAnalysis($analysisOptions, $containerDefinition);
        }
    }

    public function emitBeforeBootstrap(BootstrappingConfiguration $bootstrappingConfiguration) : void {
        foreach ($this->beforeBootstraps as $beforeBootstrap) {
            $beforeBootstrap->handleBeforeBootstrap($bootstrappingConfiguration);
        }
    }

    public function emitAfterBootstrap(BootstrappingConfiguration $bootstrappingConfiguration, ContainerDefinition $containerDefinition, AnnotatedContainer $container, ContainerAnalytics $containerAnalytics,) : void {
        foreach ($this->afterBootstraps as $afterBootstrap) {
            $afterBootstrap->handleAfterBootstrap($bootstrappingConfiguration, $containerDefinition, $container, $containerAnalytics);
        }
    }

    public function emitBeforeContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition) : void {
        foreach ($this->beforeContainerCreations as $beforeContainerCreation) {
            $beforeContainerCreation->handleBeforeContainerCreation($profiles, $containerDefinition);
        }
    }

    public function emitServiceFilteredDueToProfiles(Profiles $profiles, ServiceDefinition $serviceDefinition) : void {
        foreach ($this->serviceFilteredDueToProfiles as $serviceFilteredDueToProfile) {
            $serviceFilteredDueToProfile->handleServiceFilteredDueToProfiles($profiles, $serviceDefinition);
        }
    }

    public function emitServiceShared(Profiles $profiles, ServiceDefinition $serviceDefinition) : void {
        foreach ($this->serviceShared as $serviceShared) {
            $serviceShared->handleServiceShared($profiles, $serviceDefinition);
        }
    }

    public function emitInjectingMethodParameter(Profiles $profiles, InjectDefinition $injectDefinition) : void {
        foreach ($this->injectingMethodParameters as $injectingMethodParameter) {
            $injectingMethodParameter->handleInjectingMethodParameter($profiles, $injectDefinition);
        }
    }

    public function emitInjectingProperty(Profiles $profiles, InjectDefinition $injectDefinition) : void {
        foreach ($this->injectingProperties as $injectingProperty) {
            $injectingProperty->handleInjectingProperty($profiles, $injectDefinition);
        }
    }

    public function emitServicePrepared(Profiles $profiles, ServicePrepareDefinition $servicePrepareDefinition) : void {
        foreach ($this->servicePrepareds as $servicePrepared) {
            $servicePrepared->handleServicePrepared($profiles, $servicePrepareDefinition);
        }
    }

    public function emitServiceDelegated(Profiles $profiles, ServiceDelegateDefinition $serviceDelegateDefinition) : void {
        foreach ($this->serviceDelegateds as $serviceDelegated) {
            $serviceDelegated->handleServiceDelegated($profiles, $serviceDelegateDefinition);
        }
    }

    public function emitServiceAliasResolution(Profiles $profiles, AliasDefinition $aliasDefinition, AliasResolutionReason $resolutionReason) : void {
        foreach ($this->serviceAliasResolutions as $serviceAliasResolution) {
            $serviceAliasResolution->handleServiceAliasResolution($profiles, $aliasDefinition, $resolutionReason);
        }
    }

    public function emitAfterContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        foreach ($this->afterContainerCreation as $afterContainerCreation) {
            $afterContainerCreation->handleAfterContainerCreation($profiles, $containerDefinition, $container);
        }
    }
}

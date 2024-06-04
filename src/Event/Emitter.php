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
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\AfterBootstrap;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\BeforeBootstrap;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\AfterContainerCreation;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\BeforeContainerCreation;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\InjectingMethodParameter;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\InjectingProperty;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceAliasResolution;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceDelegated;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceFilteredDueToProfiles;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServicePrepared;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceShared;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedAliasDefinition;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedInjectDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedServiceDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedServiceDelegateDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedServicePrepareDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AfterContainerAnalysis;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedContainerDefinitionFromCache;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedInjectDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServiceDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServiceDelegateDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServicePrepareDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\BeforeContainerAnalysis;
use Cspray\AnnotatedContainer\Exception\InvalidListener;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedTarget\AnnotatedTarget;

/**
 * @psalm-type Listeners = BeforeBootstrap|BeforeContainerAnalysis
 */
final class Emitter implements StaticAnalysisEmitter, BootstrapEmitter, ContainerFactoryEmitter {

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
     * @var list<AddedAliasDefinition>
     */
    private array $addedAliasDefinitions = [];

    /**
     * @var list<AddedInjectDefinitionFromApi>
     */
    private array $addedInjectDefinitions = [];

    /**
     * @var list<AddedServiceDefinitionFromApi>
     */
    private array $addedServiceDefinitions = [];

    /**
     * @var list<AddedServiceDelegateDefinitionFromApi>
     */
    private array $addedServiceDelegateDefinitions = [];

    /**
     * @var list<AddedServicePrepareDefinitionFromApi>
     */
    private array $addedServicePrepareDefinitions = [];

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

    public function addListener(Listener $listener) : void {
        /** @var array<class-string<Listener>, callable> $listenersMap */
        $listenersMap = [
            AfterBootstrap::class => fn(AfterBootstrap $afterBootstrap) : AfterBootstrap =>
                $this->afterBootstraps[] = $afterBootstrap,
            BeforeBootstrap::class => fn(BeforeBootstrap $beforeBootstrap) : BeforeBootstrap =>
                $this->beforeBootstraps[] = $beforeBootstrap,
            AfterContainerCreation::class => fn(AfterContainerCreation $afterContainerCreation) : AfterContainerCreation =>
                $this->afterContainerCreation[] = $afterContainerCreation,
            BeforeContainerCreation::class => fn(BeforeContainerCreation $beforeContainerCreation) : BeforeContainerCreation =>
                $this->beforeContainerCreations[] = $beforeContainerCreation,
            InjectingMethodParameter::class => fn(InjectingMethodParameter $injectingMethodParameter) : InjectingMethodParameter =>
                $this->injectingMethodParameters[] = $injectingMethodParameter,
            ServiceAliasResolution::class => fn(ServiceAliasResolution $serviceAliasResolution) : ServiceAliasResolution =>
                $this->serviceAliasResolutions[] = $serviceAliasResolution,
            ServiceDelegated::class => fn(ServiceDelegated $serviceDelegated) : ServiceDelegated =>
                $this->serviceDelegateds[] = $serviceDelegated,
            ServiceFilteredDueToProfiles::class => fn(ServiceFilteredDueToProfiles $serviceFilteredDueToProfiles) : ServiceFilteredDueToProfiles =>
                $this->serviceFilteredDueToProfiles[] = $serviceFilteredDueToProfiles,
            ServicePrepared::class => fn(ServicePrepared $servicePrepared) : ServicePrepared =>
                $this->servicePrepareds[] = $servicePrepared,
            ServiceShared::class => fn(ServiceShared $serviceShared) : ServiceShared =>
                $this->serviceShared[] = $serviceShared,
            AddedAliasDefinition::class => fn(AddedAliasDefinition $addedAliasDefinition) : AddedAliasDefinition =>
                $this->addedAliasDefinitions[] = $addedAliasDefinition,
            AddedInjectDefinitionFromApi::class => fn(AddedInjectDefinitionFromApi $addedInjectDefinitionFromApi) : AddedInjectDefinitionFromApi =>
                $this->addedInjectDefinitions[] = $addedInjectDefinitionFromApi,
            AddedServiceDefinitionFromApi::class => fn(AddedServiceDefinitionFromApi $addedServiceDefinitionFromApi) : AddedServiceDefinitionFromApi =>
                $this->addedServiceDefinitions[] = $addedServiceDefinitionFromApi,
            AddedServiceDelegateDefinitionFromApi::class => fn(AddedServiceDelegateDefinitionFromApi $addedServiceDelegateDefinitionFromApi) : AddedServiceDelegateDefinitionFromApi =>
                $this->addedServiceDelegateDefinitions[] = $addedServiceDelegateDefinitionFromApi,
            AddedServicePrepareDefinitionFromApi::class => fn(AddedServicePrepareDefinitionFromApi $addedServicePrepareDefinitionFromApi) : AddedServicePrepareDefinitionFromApi =>
                $this->addedServicePrepareDefinitions[] = $addedServicePrepareDefinitionFromApi,
            AfterContainerAnalysis::class => fn(AfterContainerAnalysis $afterContainerAnalysis) : AfterContainerAnalysis =>
                $this->afterContainerAnalysis[] = $afterContainerAnalysis,
            AnalyzedContainerDefinitionFromCache::class => fn(AnalyzedContainerDefinitionFromCache $analyzedContainerDefinitionFromCache) : AnalyzedContainerDefinitionFromCache =>
                $this->analyzedContainerDefinitionFromCaches[] = $analyzedContainerDefinitionFromCache,
            AnalyzedInjectDefinitionFromAttribute::class => fn(AnalyzedInjectDefinitionFromAttribute $analyzedInjectDefinitionFromAttribute) : AnalyzedInjectDefinitionFromAttribute =>
                $this->analyzedInjectDefinitionFromAttributes[] = $analyzedInjectDefinitionFromAttribute,
            AnalyzedServiceDefinitionFromAttribute::class => fn(AnalyzedServiceDefinitionFromAttribute $analyzedServiceDefinitionFromAttribute) : AnalyzedServiceDefinitionFromAttribute =>
                $this->analyzedServiceDefinitionFromAttributes[] = $analyzedServiceDefinitionFromAttribute,
            AnalyzedServiceDelegateDefinitionFromAttribute::class => fn(AnalyzedServiceDelegateDefinitionFromAttribute $analyzedServiceDelegateDefinitionFromAttribute) : AnalyzedServiceDelegateDefinitionFromAttribute =>
                $this->analyzedServiceDelegateDefinitionFromAttributes[] = $analyzedServiceDelegateDefinitionFromAttribute,
            AnalyzedServicePrepareDefinitionFromAttribute::class => fn(AnalyzedServicePrepareDefinitionFromAttribute $analyzedServicePrepareDefinitionFromAttribute) : AnalyzedServicePrepareDefinitionFromAttribute =>
                $this->analyzedServicePrepareDefinitionFromAttributes[] = $analyzedServicePrepareDefinitionFromAttribute,
            BeforeContainerAnalysis::class => fn(BeforeContainerAnalysis $beforeContainerAnalysis) : BeforeContainerAnalysis =>
                $this->beforeContainerAnalysis[] = $beforeContainerAnalysis,
        ];

        $added = false;
        foreach ($listenersMap as $listenerType => $callable) {
            if ($listener instanceof $listenerType) {
                $added = true;
                $callable($listener);
            }
        }

        if (!$added) {
            throw InvalidListener::fromListenerNotKnownType($listener);
        }
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

    public function emitAddedAliasDefinition(AliasDefinition $aliasDefinition) : void {
        foreach ($this->addedAliasDefinitions as $addedAliasDefinition) {
            $addedAliasDefinition->handleAddedAliasDefinition($aliasDefinition);
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

    public function emitAddedInjectDefinitionFromApi(InjectDefinition $injectDefinition) : void {
        foreach ($this->addedInjectDefinitions as $addedInjectDefinition) {
            $addedInjectDefinition->handleAddedInjectDefinitionFromApi($injectDefinition);
        }
    }

    public function emitAddedServiceDefinitionFromApi(ServiceDefinition $serviceDefinition) : void {
        foreach ($this->addedServiceDefinitions as $addedServiceDefinition) {
            $addedServiceDefinition->handleAddedServiceDefinitionFromApi($serviceDefinition);
        }
    }

    public function emitAddedServiceDelegateDefinitionFromApi(ServiceDelegateDefinition $serviceDelegateDefinition) : void {
        foreach ($this->addedServiceDelegateDefinitions as $addedServiceDelegateDefinition) {
            $addedServiceDelegateDefinition->handleAddedServiceDelegateDefinitionFromApi($serviceDelegateDefinition);
        }
    }

    public function emitAddedServicePrepareDefinitionFromApi(ServicePrepareDefinition $servicePrepareDefinition) : void {
        foreach ($this->addedServicePrepareDefinitions as $addedServicePrepareDefinition) {
            $addedServicePrepareDefinition->handleAddedServicePrepareDefinitionFromApi($servicePrepareDefinition);
        }
    }
}

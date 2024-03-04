<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Auryn\Injector;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\BootstrapEmitter;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Exception\BackingContainerNotFound;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\PrecisionStopwatch\Marker;
use Cspray\PrecisionStopwatch\Metrics;
use Cspray\PrecisionStopwatch\Stopwatch;
use DI\Container;

final class Bootstrap {

    private readonly ?BootstrapEmitter $emitter;

    private readonly BootstrappingDirectoryResolver $directoryResolver;
    private readonly ParameterStoreFactory $parameterStoreFactory;

    private readonly Stopwatch $stopwatch;

    /**
     * @var list<PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver|ContainerAnalyticsObserver>
     */
    private array $observers = [];

    public function __construct(
        BootstrapEmitter $emitter = null,
        BootstrappingDirectoryResolver $directoryResolver = null,
        ParameterStoreFactory $parameterStoreFactory = null,
        private readonly ?DefinitionProviderFactory $definitionProviderFactory = null,
        private readonly ?ObserverFactory $observerFactory = null,
        Stopwatch $stopwatch = null,
        private readonly ?ContainerFactory $containerFactory = null
    ) {
        $this->emitter = $emitter;
        $this->directoryResolver = $directoryResolver ?? $this->defaultDirectoryResolver();
        $this->parameterStoreFactory = $parameterStoreFactory ?? new DefaultParameterStoreFactory();
        $this->stopwatch = $stopwatch ?? new Stopwatch();

        if ($this->observerFactory !== null) {
            trigger_error(
                'The Observer system is being replaced in 3.0 with an Event system. There will no longer be a ' . ObserverFactory::class . ' and will be removed as a dependency to ' . Bootstrap::class,
                E_USER_DEPRECATED
            );
        }
    }

    private function defaultDirectoryResolver() : BootstrappingDirectoryResolver {
        $rootDir = dirname(__DIR__);
        if (!file_exists($rootDir . '/vendor/autoload.php')) {
            $rootDir = dirname(__DIR__, 5);
        }

        return new RootDirectoryBootstrappingDirectoryResolver($rootDir);
    }

    public function addObserver(PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver|ContainerAnalyticsObserver $observer) : void {
        trigger_error(
            'The Observer system is being removed from ' . Bootstrap::class . ' in 3.0.',
            E_USER_DEPRECATED
        );
        $this->observers[] = $observer;
    }

    public function bootstrapContainer(
        Profiles $profiles,
        string $configurationFile = 'annotated-container.xml'
    ) : AnnotatedContainer {

        $this->stopwatch->start();

        $configuration = $this->bootstrappingConfiguration($configurationFile);
        $analysisOptions = $this->analysisOptions($configuration);

        $this->emitter?->emitBeforeBootstrap($configuration);

        foreach ($configuration->getObservers() as $observer) {
            $this->addObserver($observer);
        }

        $this->notifyPreAnalysis($profiles);

        $analysisPrepped = $this->stopwatch->mark();

        $containerDefinition = $this->runStaticAnalysis($configuration, $analysisOptions);
        $this->notifyPostAnalysis($profiles, $containerDefinition);

        $analysisCompleted = $this->stopwatch->mark();

        $container = $this->createContainer(
            $configuration,
            $profiles,
            $containerDefinition,
        );

        $this->notifyContainerCreated($profiles, $containerDefinition, $container);

        $metrics = $this->stopwatch->stop();
        $analytics = $this->createAnalytics($metrics, $analysisPrepped, $analysisCompleted);
        $this->notifyAnalytics($analytics);

        $this->emitter?->emitAfterBootstrap(
            $configuration,
            $containerDefinition,
            $container,
            $analytics
        );

        return $container;
    }

    private function bootstrappingConfiguration(string $configurationFile) : BootstrappingConfiguration {
        $configFile = $this->directoryResolver->getConfigurationPath($configurationFile);
        return new XmlBootstrappingConfiguration(
            $configFile,
            parameterStoreFactory: $this->parameterStoreFactory,
            observerFactory: $this->observerFactory,
            definitionProviderFactory: $this->definitionProviderFactory
        );
    }


    private function analysisOptions(BootstrappingConfiguration $configuration) : ContainerDefinitionAnalysisOptions {
        $scanPaths = [];
        foreach ($configuration->getScanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->getPathFromRoot($scanDirectory);
        }
        $analysisOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$scanPaths);
        $containerDefinitionConsumer = $configuration->getContainerDefinitionProvider();
        if ($containerDefinitionConsumer !== null) {
            $analysisOptions = $analysisOptions->withDefinitionProvider($containerDefinitionConsumer);
        }

        return $analysisOptions->build();
    }

    private function notifyPreAnalysis(Profiles $activeProfiles) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof PreAnalysisObserver) {
                $observer->notifyPreAnalysis($activeProfiles);
            }
        }
    }

    private function runStaticAnalysis(
        BootstrappingConfiguration $configuration,
        ContainerDefinitionAnalysisOptions $analysisOptions
    ) : ContainerDefinition {
        $cacheDir = null;
        $configuredCacheDir = $configuration->getCacheDirectory();
        if ($configuredCacheDir !== null) {
            $cacheDir = $this->directoryResolver->getCachePath($configuredCacheDir);
        }
        return $this->containerDefinitionAnalyzer($cacheDir)->analyze($analysisOptions);
    }

    private function containerDefinitionAnalyzer(?string $cacheDir) : ContainerDefinitionAnalyzer {
        $compiler = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
        if ($cacheDir !== null) {
            $compiler = new CacheAwareContainerDefinitionAnalyzer($compiler, new ContainerDefinitionSerializer(), $cacheDir);
        }

        return $compiler;
    }

    private function notifyPostAnalysis(Profiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof PostAnalysisObserver) {
                $observer->notifyPostAnalysis($activeProfiles, $containerDefinition);
            }
        }
    }

    private function createContainer(
        BootstrappingConfiguration $configuration,
        Profiles $activeProfiles,
        ContainerDefinition $containerDefinition,
    ) : AnnotatedContainer {
        $containerFactory = $this->containerFactory();

        foreach ($configuration->getParameterStores() as $parameterStore) {
            $containerFactory->addParameterStore($parameterStore);
        }

        $factoryOptions = ContainerFactoryOptionsBuilder::forProfiles($activeProfiles);

        return $containerFactory->createContainer($containerDefinition, $factoryOptions->build());
    }

    private function containerFactory() : ContainerFactory {
        if ($this->containerFactory !== null) {
            return $this->containerFactory;
        }

        if (class_exists(Injector::class)) {
            return new AurynContainerFactory();
        }

        if (class_exists(Container::class)) {
            return new PhpDiContainerFactory();
        }

        throw BackingContainerNotFound::fromMissingImplementation();
    }

    private function notifyContainerCreated(
        Profiles $activeProfiles,
        ContainerDefinition $containerDefinition,
        AnnotatedContainer $container
    ) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof ContainerCreatedObserver) {
                $observer->notifyContainerCreated($activeProfiles, $containerDefinition, $container);
            }
        }
    }

    private function createAnalytics(
        Metrics $metrics,
        Marker $prepCompleted,
        Marker $analysisCompleted
    ) : ContainerAnalytics {
        return new ContainerAnalytics(
            $metrics->getTotalDuration(),
            $metrics->getDurationBetweenMarkers($metrics->getStartMarker(), $prepCompleted),
            $metrics->getDurationBetweenMarkers($prepCompleted, $analysisCompleted),
            $metrics->getDurationBetweenMarkers($analysisCompleted, $metrics->getEndMarker())
        );
    }

    private function notifyAnalytics(ContainerAnalytics $analytics) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof ContainerAnalyticsObserver) {
                $observer->notifyAnalytics($analytics);
            }
        }
    }
}

<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Auryn\Injector;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
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
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\PrecisionStopwatch\Marker;
use Cspray\PrecisionStopwatch\Metrics;
use Cspray\PrecisionStopwatch\Stopwatch;
use DI\Container;
use Psr\Log\LoggerInterface;

final class Bootstrap {

    private readonly BootstrappingDirectoryResolver $directoryResolver;
    private readonly ParameterStoreFactory $parameterStoreFactory;

    private readonly Stopwatch $stopwatch;

    /**
     * @var list<PreAnalysisObserver|PostAnalysisObserver|ContainerCreatedObserver|ContainerAnalyticsObserver>
     */
    private array $observers = [];

    public function __construct(
        BootstrappingDirectoryResolver $directoryResolver = null,
        /**
         * @deprecated
         */
        private readonly ?LoggerInterface $logger = null,
        ParameterStoreFactory $parameterStoreFactory = null,
        private readonly ?DefinitionProviderFactory $definitionProviderFactory = null,
        private readonly ?ObserverFactory $observerFactory = null,
        Stopwatch $stopwatch = null,
        private readonly ?ContainerFactory $containerFactory = null
    ) {
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

    /**
     * @param list<non-empty-string> $profiles
     * @throws BackingContainerNotFound
     * @throws InvalidBootstrapConfiguration
     */
    public function bootstrapContainer(
        array $profiles = ['default'],
        string $configurationFile = 'annotated-container.xml'
    ) : AnnotatedContainer {

        $this->stopwatch->start();

        $configuration = $this->bootstrappingConfiguration($configurationFile);
        $analysisOptions = $this->analysisOptions($configuration, $profiles);

        foreach ($configuration->getObservers() as $observer) {
            $this->addObserver($observer);
        }

        $activeProfiles = new class($profiles) implements ActiveProfiles {
            public function __construct(
                /** @var list<non-empty-string> */
                private readonly array $profiles
            ) {
            }

            public function getProfiles() : array {
                trigger_error(
                    'The ' . ActiveProfiles::class . ' interface is being removed in 3.0. Please use the new Profiles interface instead.',
                    E_USER_DEPRECATED
                );
                return $this->profiles;
            }

            public function isActive(string $profile) : bool {
                trigger_error(
                    'The ' . ActiveProfiles::class . ' interface is being removed in 3.0. Please use the new Profiles interface instead.',
                    E_USER_DEPRECATED
                );
                return in_array($profile, $this->profiles, true);
            }
        };

        $this->notifyPreAnalysis($activeProfiles);

        $analysisPrepped = $this->stopwatch->mark();

        $containerDefinition = $this->runStaticAnalysis($configuration, $analysisOptions);
        $this->notifyPostAnalysis($activeProfiles, $containerDefinition);

        $analysisCompleted = $this->stopwatch->mark();

        $container = $this->createContainer(
            $configuration,
            $activeProfiles,
            $containerDefinition,
            $analysisOptions->getLogger()
        );

        $this->notifyContainerCreated($activeProfiles, $containerDefinition, $container);

        $metrics = $this->stopwatch->stop();
        $analytics = $this->createAnalytics($metrics, $analysisPrepped, $analysisCompleted);
        $this->notifyAnalytics($analytics);

        $message = sprintf(
            'Took %fms to analyze and create your container. %fms was spent preparing for analysis. %fms was spent statically analyzing your codebase. %fms was spent wiring your container.',
            $analytics->totalTime->timeTakenInMilliseconds(),
            $analytics->timePreppingForAnalysis->timeTakenInMilliseconds(),
            $analytics->timeTakenForAnalysis->timeTakenInMilliseconds(),
            $analytics->timeTakenCreatingContainer->timeTakenInMilliseconds()
        );
        $analysisOptions->getLogger()?->info(
            $message,
            [
                'total_time_in_ms' => $analytics->totalTime->timeTakenInMilliseconds(),
                'pre_analysis_time_in_ms' => $analytics->timePreppingForAnalysis->timeTakenInMilliseconds(),
                'post_analysis_time_in_ms' => $analytics->timeTakenForAnalysis->timeTakenInMilliseconds(),
                'container_wired_time_in_ms' => $analytics->timeTakenCreatingContainer->timeTakenInMilliseconds()
            ]
        );
        return $container;
    }

    private function bootstrappingConfiguration(string $configurationFile) : BootstrappingConfiguration {
        $configFile = $this->directoryResolver->getConfigurationPath($configurationFile);
        return new XmlBootstrappingConfiguration(
            $configFile,
            directoryResolver: $this->directoryResolver,
            parameterStoreFactory: $this->parameterStoreFactory,
            observerFactory: $this->observerFactory,
            definitionProviderFactory: $this->definitionProviderFactory
        );
    }


    private function analysisOptions(BootstrappingConfiguration $configuration, array $activeProfiles) : ContainerDefinitionAnalysisOptions {
        $scanPaths = [];
        foreach ($configuration->getScanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->getPathFromRoot($scanDirectory);
        }
        $analysisOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$scanPaths);
        $containerDefinitionConsumer = $configuration->getContainerDefinitionProvider();
        if ($containerDefinitionConsumer !== null) {
            $analysisOptions = $analysisOptions->withDefinitionProvider($containerDefinitionConsumer);
        }

        $profilesAllowLogging = count(array_intersect($activeProfiles, $configuration->getLoggingExcludedProfiles())) === 0;
        $logger = $this->logger ?? $configuration->getLogger();
        if ($logger !== null && $profilesAllowLogging) {
            $analysisOptions = $analysisOptions->withLogger($logger);
        }

        return $analysisOptions->build();
    }

    private function notifyPreAnalysis(ActiveProfiles $activeProfiles) : void {
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

    private function notifyPostAnalysis(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition) : void {
        foreach ($this->observers as $observer) {
            if ($observer instanceof PostAnalysisObserver) {
                $observer->notifyPostAnalysis($activeProfiles, $containerDefinition);
            }
        }
    }

    private function createContainer(
        BootstrappingConfiguration $configuration,
        ActiveProfiles $activeProfiles,
        ContainerDefinition $containerDefinition,
        ?LoggerInterface $logger
    ) : AnnotatedContainer {
        $containerFactory = $this->containerFactory();

        foreach ($configuration->getParameterStores() as $parameterStore) {
            $containerFactory->addParameterStore($parameterStore);
        }

        $factoryOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$activeProfiles->getProfiles());
        if ($logger !== null) {
            $factoryOptions = $factoryOptions->withLogger($logger);
        }

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
        ActiveProfiles $activeProfiles,
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

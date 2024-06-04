<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

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
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\PrecisionStopwatch\Marker;
use Cspray\PrecisionStopwatch\Metrics;
use Cspray\PrecisionStopwatch\Stopwatch;

final class Bootstrap {

    private readonly BootstrappingDirectoryResolver $directoryResolver;

    public function __construct(
        private readonly ContainerFactory $containerFactory,
        private readonly ?BootstrapEmitter $emitter = null,
        BootstrappingDirectoryResolver $directoryResolver = null,
        private readonly ParameterStoreFactory $parameterStoreFactory = new DefaultParameterStoreFactory(),
        private readonly ?DefinitionProviderFactory $definitionProviderFactory = null,
        private readonly Stopwatch $stopwatch = new Stopwatch(),
    ) {
        $this->directoryResolver = $directoryResolver ?? $this->defaultDirectoryResolver();
    }

    private function defaultDirectoryResolver() : BootstrappingDirectoryResolver {
        $rootDir = dirname(__DIR__);
        if (!file_exists($rootDir . '/vendor/autoload.php')) {
            $rootDir = dirname(__DIR__, 5);
        }

        return new RootDirectoryBootstrappingDirectoryResolver($rootDir);
    }

    public function bootstrapContainer(
        Profiles $profiles,
        string $configurationFile = 'annotated-container.xml'
    ) : AnnotatedContainer {

        $this->stopwatch->start();

        $configuration = $this->bootstrappingConfiguration($configurationFile);
        $analysisOptions = $this->analysisOptions($configuration);

        $this->emitter?->emitBeforeBootstrap($configuration);

        $analysisPrepped = $this->stopwatch->mark();

        $containerDefinition = $this->runStaticAnalysis($configuration, $analysisOptions);

        $analysisCompleted = $this->stopwatch->mark();

        $container = $this->createContainer(
            $configuration,
            $profiles,
            $containerDefinition,
        );

        $metrics = $this->stopwatch->stop();
        $analytics = $this->createAnalytics($metrics, $analysisPrepped, $analysisCompleted);

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

    private function createContainer(
        BootstrappingConfiguration $configuration,
        Profiles $activeProfiles,
        ContainerDefinition $containerDefinition,
    ) : AnnotatedContainer {
        foreach ($configuration->getParameterStores() as $parameterStore) {
            $this->containerFactory->addParameterStore($parameterStore);
        }

        $factoryOptions = ContainerFactoryOptionsBuilder::forProfiles($activeProfiles);

        return $this->containerFactory->createContainer($containerDefinition, $factoryOptions->build());
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
}

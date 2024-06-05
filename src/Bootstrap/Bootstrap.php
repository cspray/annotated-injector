<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Auryn\Injector as AurynContainer;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\IlluminateContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\XmlContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Event\ContainerFactoryEmitter;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\PrecisionStopwatch\Marker;
use Cspray\PrecisionStopwatch\Metrics;
use Cspray\PrecisionStopwatch\Stopwatch;
use DI\Container as PhpDiContainer;
use Illuminate\Container\Container as IlluminateContainer;
use RuntimeException;

final class Bootstrap {

    private function __construct(
        private readonly ContainerFactory $containerFactory,
        private readonly Emitter $emitter,
        private readonly BootstrappingDirectoryResolver $directoryResolver,
        private readonly ParameterStoreFactory $parameterStoreFactory,
        private readonly DefinitionProviderFactory $definitionProviderFactory,
        private readonly Stopwatch $stopwatch,
    ) {
    }

    public static function fromMinimalSetup(
        Emitter $emitter,
        ParameterStoreFactory $parameterStoreFactory = new DefaultParameterStoreFactory(),
        DefinitionProviderFactory $definitionProviderFactory = new DefaultDefinitionProviderFactory(),
    ) : self {
        $containerFactory = self::inferredContainerFactory($emitter);
        return new Bootstrap(
            $containerFactory,
            $emitter,
            new VendorPresenceBasedBootstrappingDirectoryResolver(),
            $parameterStoreFactory,
            $definitionProviderFactory,
            new Stopwatch()
        );
    }

    private static function inferredContainerFactory(
        ContainerFactoryEmitter $emitter,
    ) : ContainerFactory {
        if (class_exists(PhpDiContainer::class)) {
            return new PhpDiContainerFactory($emitter);
        }

        if (class_exists(IlluminateContainer::class)) {
            return new IlluminateContainerFactory($emitter);
        }

        if (class_exists(AurynContainer::class)) {
            return new AurynContainerFactory($emitter);
        }

        throw new RuntimeException(
            'To utilize Bootstrap::fromMinimalSetup you MUST install a backing container listed when running "composer suggest".'
        );
    }

    public static function fromCompleteSetup(
        ContainerFactory $containerFactory,
        Emitter $emitter,
        BootstrappingDirectoryResolver $resolver,
        ParameterStoreFactory $parameterStoreFactory,
        DefinitionProviderFactory $definitionProviderFactory,
        Stopwatch $stopwatch = new Stopwatch()
    ) : self {
        return new Bootstrap(
            $containerFactory,
            $emitter,
            $resolver,
            $parameterStoreFactory,
            $definitionProviderFactory,
            $stopwatch
        );
    }

    public function bootstrapContainer(
        Profiles $profiles = null,
        BootstrappingConfigurationProvider $bootstrappingConfigurationProvider = new XmlBootstrappingConfigurationProvider()
    ) : AnnotatedContainer {
        $profiles ??= Profiles::defaultOnly();

        $this->stopwatch->start();

        $configuration = $bootstrappingConfigurationProvider->bootstrappingConfiguration(
            $this->directoryResolver,
            $this->parameterStoreFactory,
            $this->definitionProviderFactory
        );
        $analysisOptions = $this->analysisOptions($configuration);

        $this->emitter->emitBeforeBootstrap($configuration);

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

        $this->emitter->emitAfterBootstrap(
            $configuration,
            $containerDefinition,
            $container,
            $analytics
        );

        return $container;
    }

    private function analysisOptions(BootstrappingConfiguration $configuration) : ContainerDefinitionAnalysisOptions {
        $scanPaths = [];
        foreach ($configuration->scanDirectories() as $scanDirectory) {
            $scanPaths[] = $this->directoryResolver->pathFromRoot($scanDirectory);
        }
        $analysisOptions = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(...$scanPaths);
        $containerDefinitionConsumer = $configuration->containerDefinitionProvider();
        if ($containerDefinitionConsumer !== null) {
            $analysisOptions = $analysisOptions->withDefinitionProvider($containerDefinitionConsumer);
        }

        return $analysisOptions->build();
    }

    private function runStaticAnalysis(
        BootstrappingConfiguration $configuration,
        ContainerDefinitionAnalysisOptions $analysisOptions
    ) : ContainerDefinition {
        $analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter(),
            $this->emitter
        );
        $cache = $configuration->cache();
        if ($cache !== null) {
            $analyzer = new CacheAwareContainerDefinitionAnalyzer(
                $analyzer,
                $cache
            );
        }

        return $analyzer->analyze($analysisOptions);
    }

    private function createContainer(
        BootstrappingConfiguration $configuration,
        Profiles $activeProfiles,
        ContainerDefinition $containerDefinition,
    ) : AnnotatedContainer {
        foreach ($configuration->parameterStores() as $parameterStore) {
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

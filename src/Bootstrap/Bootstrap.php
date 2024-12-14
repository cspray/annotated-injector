<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\Filesystem\PhpFunctionsFilesystem;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\CacheAwareContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\PrecisionStopwatch\Marker;
use Cspray\PrecisionStopwatch\Metrics;
use Cspray\PrecisionStopwatch\Stopwatch;

final class Bootstrap {

    private function __construct(
        private readonly BootstrappingConfiguration $bootstrappingConfiguration,
        private readonly ContainerFactory $containerFactory,
        private readonly Emitter $emitter,
        private readonly BootstrappingDirectoryResolver $directoryResolver,
        private readonly Stopwatch $stopwatch,
    ) {
    }

    public static function fromAnnotatedContainerConventions(
        ContainerFactory $containerFactory,
        Emitter $emitter,
        ParameterStoreFactory $parameterStoreFactory = new DefaultParameterStoreFactory(),
        DefinitionProviderFactory $definitionProviderFactory = new DefaultDefinitionProviderFactory(),
        BootstrappingDirectoryResolver $directoryResolver = new VendorPresenceBasedBootstrappingDirectoryResolver(),
        Filesystem $filesystem = new PhpFunctionsFilesystem()
    ) : self {
        $configuration = new XmlBootstrappingConfiguration(
            $filesystem,
            $directoryResolver->configurationPath('annotated-container.xml'),
            $parameterStoreFactory,
            $definitionProviderFactory
        );

        return self::fromCompleteSetup(
            $configuration,
            $containerFactory,
            $emitter,
            $directoryResolver,
        );
    }

    public static function fromCompleteSetup(
        BootstrappingConfiguration $bootstrappingConfiguration,
        ContainerFactory $containerFactory,
        Emitter $emitter,
        BootstrappingDirectoryResolver $resolver,
        Stopwatch $stopwatch = new Stopwatch()
    ) : self {
        return new Bootstrap(
            $bootstrappingConfiguration,
            $containerFactory,
            $emitter,
            $resolver,
            $stopwatch
        );
    }

    public function bootstrapContainer(
        Profiles $profiles = null,
    ) : AnnotatedContainer {
        $profiles ??= Profiles::defaultOnly();

        $this->stopwatch->start();

        $analysisOptions = $this->analysisOptions($this->bootstrappingConfiguration);

        $this->emitter->emitBeforeBootstrap($this->bootstrappingConfiguration);

        $analysisPrepped = $this->stopwatch->mark();

        $containerDefinition = $this->runStaticAnalysis($this->bootstrappingConfiguration, $analysisOptions);

        $analysisCompleted = $this->stopwatch->mark();

        $container = $this->createContainer(
            $this->bootstrappingConfiguration,
            $profiles,
            $containerDefinition,
        );

        $metrics = $this->stopwatch->stop();
        $analytics = $this->createAnalytics($metrics, $analysisPrepped, $analysisCompleted);

        $this->emitter->emitAfterBootstrap(
            $this->bootstrappingConfiguration,
            $containerDefinition,
            $container,
            $analytics
        );

        return $container;
    }

    private function analysisOptions(BootstrappingConfiguration $configuration) : ContainerDefinitionAnalysisOptions {
        return (new ContainerDefinitionAnalysisOptionsFromBootstrappingConfiguration(
            $configuration,
            $this->directoryResolver
        ))->create();
    }

    private function runStaticAnalysis(
        BootstrappingConfiguration $configuration,
        ContainerDefinitionAnalysisOptions $analysisOptions
    ) : ContainerDefinition {
        $analyzer = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
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

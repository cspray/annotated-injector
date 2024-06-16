<?php

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Bootstrap\CacheAwareBootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Bootstrap\ServiceFromServiceDefinition;
use Cspray\AnnotatedContainer\Bootstrap\ServiceGatherer;
use Cspray\AnnotatedContainer\Bootstrap\ServiceWiringListener;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\ContainerFactory\PhpDiContainerFactory;
use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\AfterBootstrap;
use Cspray\AnnotatedContainer\Exception\InvalidBootstrapConfiguration;
use Cspray\AnnotatedContainer\Filesystem\Filesystem;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapListener;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use Cspray\AnnotatedContainer\Fixture\CustomServiceAttribute\Repository;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\PrecisionStopwatch\KnownIncrementingPreciseTime;
use Cspray\PrecisionStopwatch\Stopwatch;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;

final class BootstrapTest extends TestCase {

    private VirtualDirectory $vfs;

    /**
     * @param list<string> $scanDirectories
     * @param list<ParameterStore> $parameterStores
     */
    private function bootstrappingConfigurationMock(
        array $scanDirectories,
        DefinitionProvider $definitionProvider = null,
        ContainerDefinitionCache $cache = null,
        array $parameterStores = []
    ) : BootstrappingConfiguration {
        $configuration = $this->createMock(BootstrappingConfiguration::class);
        $configuration->method('scanDirectories')->willReturn($scanDirectories);
        $configuration->method('containerDefinitionProvider')->willReturn($definitionProvider);
        $configuration->method('cache')->willReturn($cache);
        $configuration->method('parameterStores')->willReturn($parameterStores);

        return $configuration;
    }

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testBootstrapSingleConcreteServiceNoCache() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();

        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['SingleConcreteService']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }

    public function testBootstrapWithValidDefinitionProvider() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(
                ['ThirdPartyServices'],
                new StubDefinitionProvider()
            ),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::thirdPartyServices()->fooInterface()->getName());
        self::assertInstanceOf(Fixtures::thirdPartyServices()->fooImplementation()->getName(), $service);
    }

    public function testBootstrapWithParameterStores() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(
                ['InjectCustomStoreServices'],
                parameterStores: [new StubParameterStore()]
            ),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());
        self::assertInstanceOf(Fixtures::injectCustomStoreServices()->scalarInjector()->getName(), $service);
        self::assertSame('from test-store key', $service->key);
    }

    public function testBootstrapResolvesProfileServices() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['ProfileResolvedServices']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer(profiles: Profiles::fromList(['default', 'dev']));
        $service = $container->get(Fixtures::profileResolvedServices()->fooInterface()->getName());
        self::assertInstanceOf(Fixtures::profileResolvedServices()->devImplementation()->getName(), $service);
    }

    public function testServiceWiringObserver() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['AmbiguousAliasedServices']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->servicesForType(Fixtures::ambiguousAliasedServices()->fooInterface()->getName());
            }
        };

        $emitter->addListener($listener);

        $container = $bootstrap->bootstrapContainer();

        $actual = $listener->getServices();

        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->service(), $actual);

        usort($actualServices, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertSame([
            $container->get(Fixtures::ambiguousAliasedServices()->barImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->bazImplementation()->getName()),
            $container->get(Fixtures::ambiguousAliasedServices()->quxImplementation()->getName()),
        ], $actualServices);
    }

    public function testServiceWiringObserverByAttributes() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['CustomServiceAttribute']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->servicesWithAttribute(Repository::class);
            }
        };

        $emitter->addListener($listener);

        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default', 'test']));

        $actual = $listener->getServices();
        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->service(), $actual);

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertSame([
            $container->get(Fixtures::customServiceAttribute()->myRepo()->getName()),
        ], $actualServices);
    }

    public function testServiceWiringObserverByTypeProfileAware() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();

        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['ProfileResolvedServices']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->servicesForType(Fixtures::profileResolvedServices()->fooInterface()->getName());
            }
        };

        $emitter->addListener($listener);

        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default', 'prod']));

        $actual = $listener->getServices();

        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->service(), $actual);
        $actualDefinitions = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->definition(), $actual);

        usort($actualServices, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertSame([
            $container->get(Fixtures::profileResolvedServices()->prodImplementation()->getName()),
        ], $actualServices);
        self::assertCount(1, $actualDefinitions);
    }

    public function testServiceWiringObserverByAttributesProfileAware() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $emitter = new Emitter();

        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['CustomServiceAttribute']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );

        $listener = new class extends ServiceWiringListener {

            private ?AnnotatedContainer $container = null;
            private array $services = [];

            public function getAnnotatedContainer() : ?AnnotatedContainer {
                return $this->container;
            }

            public function getServices() : array {
                return $this->services;
            }

            protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void {
                $this->container = $container;
                $this->services = $gatherer->servicesWithAttribute(Repository::class);
            }
        };

        $emitter->addListener($listener);

        // The Repository is only active under 'test' profile and should not be included
        $container = $bootstrap->bootstrapContainer(Profiles::fromList(['default', 'dev']));

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertEmpty($listener->getServices());
    }

    public function testContainerAnalyticsHasExpectedTotalDuration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $listener = new class implements AfterBootstrap {
            private ?ContainerAnalytics $analytics = null;

            public function getAnalytics() : ?ContainerAnalytics {
                return $this->analytics;
            }

            public function handleAfterBootstrap(BootstrappingConfiguration $bootstrappingConfiguration, ContainerDefinition $containerDefinition, AnnotatedContainer $container, ContainerAnalytics $containerAnalytics,) : void {
                $this->analytics = $containerAnalytics;
            }
        };

        $emitter = new Emitter();
        $subject = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['SingleConcreteService']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch(new KnownIncrementingPreciseTime())
        );

        $emitter->addListener($listener);

        $subject->bootstrapContainer();

        $analytics = $listener->getAnalytics();
        self::assertNotNull($analytics);

        self::assertSame(3, $analytics->totalTime->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timePreppingForAnalysis->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timeTakenForAnalysis->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timeTakenCreatingContainer->timeTakenInNanoseconds());
    }

    public function testContainerFactoryPassedToConstructorTakesPriority() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->getMock();
        $containerFactory->expects($this->once())
            ->method('createContainer')
            ->willReturn($container = $this->getMockBuilder(AnnotatedContainer::class)->getMock());

        $emitter = new Emitter();
        $subject = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['SingleConcreteService']),
            $containerFactory,
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );

        $actual = $subject->bootstrapContainer();

        self::assertSame($container, $actual);
    }

    public function testBootstrapEventsTriggeredInCorrectOrder() : void {
        $emitter = new Emitter();
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $listener = new StubBootstrapListener();
        $emitter->addListener($listener);

        $bootstrap = Bootstrap::fromCompleteSetup(
            $this->bootstrappingConfigurationMock(['SingleConcreteService']),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );
        $bootstrap->bootstrapContainer();

        self::assertSame(
            [StubBootstrapListener::class . '::handleBeforeBootstrap', StubBootstrapListener::class . '::handleAfterBootstrap'],
            $listener->getTriggeredEvents()
        );
    }

    public function testBootstrapHandlesConfigurationWithCacheCorrectly() : void {
        $emitter = new Emitter();
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $cacheKey = CacheKey::fromContainerDefinitionAnalysisOptions(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                $directoryResolver->rootPath('SingleConcreteService')
            )->build()
        );
        $cache = $this->getMockBuilder(ContainerDefinitionCache::class)->getMock();
        $cache->expects($this->once())
            ->method('get')
            ->with($this->callback(fn(CacheKey $key) => $key->asString() === $cacheKey->asString()))
            ->willReturn(null);

        $bootstrap = Bootstrap::fromCompleteSetup(
            new CacheAwareBootstrappingConfiguration(
                $this->bootstrappingConfigurationMock(['SingleConcreteService']),
                $cache
            ),
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new Stopwatch()
        );
        $bootstrap->bootstrapContainer();
    }

    public function testBootstrapFromAnnotatedContainerConventionsThrowsExceptionIfConfigurationNotPresent() : void {
        $containerFactory = $this->createMock(ContainerFactory::class);
        $emitter = new Emitter();
        $directoryResolver = $this->createMock(BootstrappingDirectoryResolver::class);
        $filesystem = $this->createMock(Filesystem::class);

        $directoryResolver->expects($this->once())
            ->method('configurationPath')
            ->with('annotated-container.xml')
            ->willReturn('/path/to/annotated-container.xml');

        $filesystem->expects($this->once())
            ->method('isFile')
            ->with('/path/to/annotated-container.xml')
            ->willReturn(false);

        $this->expectException(InvalidBootstrapConfiguration::class);
        $this->expectExceptionMessage('Provided configuration file /path/to/annotated-container.xml does not exist.');

        Bootstrap::fromAnnotatedContainerConventions(
            $containerFactory,
            $emitter,
            directoryResolver: $directoryResolver,
            filesystem: $filesystem
        );
    }

    public function testBootstrapFromAnnotatedContainerConventionsWithFilePresentReturnsBootstrap() : void {
        $emitter = new Emitter();
        $containerFactory = new PhpDiContainerFactory($emitter);
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd" version="dev-main">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')->at($this->vfs)->setContent($xml);

        $bootstrap = Bootstrap::fromAnnotatedContainerConventions(
            $containerFactory,
            $emitter,
            directoryResolver: $directoryResolver
        );

        $container = $bootstrap->bootstrapContainer();
        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }
}

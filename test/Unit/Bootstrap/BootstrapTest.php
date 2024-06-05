<?php

namespace Cspray\AnnotatedContainer\Unit\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\CacheAwareBootstrappingConfigurationProvider;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Bootstrap\DefaultDefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefaultParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\ServiceFromServiceDefinition;
use Cspray\AnnotatedContainer\Bootstrap\ServiceGatherer;
use Cspray\AnnotatedContainer\Bootstrap\ServiceWiringListener;
use Cspray\AnnotatedContainer\Bootstrap\XmlBootstrappingConfigurationProvider;
use Cspray\AnnotatedContainer\ContainerFactory\AurynContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\Cache\CacheKey;
use Cspray\AnnotatedContainer\Definition\Cache\ContainerDefinitionCache;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\AfterBootstrap;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapListener;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStoreWithDependencies;
use Cspray\AnnotatedContainerFixture\CustomServiceAttribute\Repository;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\PrecisionStopwatch\KnownIncrementingPreciseTime;
use Cspray\PrecisionStopwatch\Stopwatch;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase {

    private VirtualDirectory $vfs;

    protected function setUp() : void {
        $this->vfs = VirtualFilesystem::setup();
    }

    public function testBootstrapSingleConcreteServiceNoCache() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
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

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ThirdPartyServices</dir>
        </source>
    </scanDirectories>
    <definitionProviders>
        <definitionProvider>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProvider</definitionProvider>
    </definitionProviders>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::thirdPartyServices()->fooInterface()->getName());
        self::assertInstanceOf(Fixtures::thirdPartyServices()->fooImplementation()->getName(), $service);
    }

    public function testBootstrapWithParameterStores() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
  <scanDirectories>
    <source>
      <dir>InjectCustomStoreServices</dir>
    </source>
  </scanDirectories>
  <parameterStores>
    <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore</parameterStore>
  </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());
        self::assertInstanceOf(Fixtures::injectCustomStoreServices()->scalarInjector()->getName(), $service);
        self::assertSame('from test-store key', $service->key);
    }

    public function testBootstrapResolvesProfileServices() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ProfileResolvedServices</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer(profiles: Profiles::fromList(['default', 'dev']));
        $service = $container->get(Fixtures::profileResolvedServices()->fooInterface()->getName());
        self::assertInstanceOf(Fixtures::profileResolvedServices()->devImplementation()->getName(), $service);
    }

    public function testBootstrapSingleConcreteServiceUsesCustomFileName() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('my-container.xml.dist')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        );
        $container = $bootstrap->bootstrapContainer(
            bootstrappingConfigurationProvider: new XmlBootstrappingConfigurationProvider('my-container.xml.dist')
        );

        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }

    public function testBoostrapDefinitionProviderFactoryPassedToConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ThirdPartyServices</dir>
        </source>
    </scanDirectories>
    <definitionProviders>
        <definitionProvider>Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies</definitionProvider>
    </definitionProviders>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $factory = new class implements DefinitionProviderFactory {

            public function createProvider(string $identifier) : DefinitionProvider {
                if ($identifier === StubDefinitionProviderWithDependencies::class) {
                    return new StubDefinitionProviderWithDependencies(Fixtures::thirdPartyServices()->fooImplementation());
                } else {
                    throw new \RuntimeException();
                }
            }
        };

        $emitter = new Emitter();
        $container = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            $factory,
            new Stopwatch()
        )->bootstrapContainer();

        $service = $container->get(Fixtures::thirdPartyServices()->fooInterface()->getName());

        self::assertInstanceOf(Fixtures::thirdPartyServices()->fooImplementation()->getName(), $service);
    }

    public function testBootstrapParameterStoreFactoryPassedToConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>InjectCustomStoreServices</dir>
        </source>
    </scanDirectories>
    <parameterStores>
      <parameterStore>Cspray\AnnotatedContainer\Unit\Helper\StubParameterStoreWithDependencies</parameterStore>
    </parameterStores>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $factory = new class implements ParameterStoreFactory {

            public function createParameterStore(string $identifier) : ParameterStore {
                if ($identifier === StubParameterStoreWithDependencies::class) {
                    return new StubParameterStoreWithDependencies('ac-ac');
                } else {
                    throw new \RuntimeException();
                }
            }
        };

        $emitter = new Emitter();
        $container = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            $factory,
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        )->bootstrapContainer();

        $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());

        self::assertSame('ac-ackey', $service->key);
    }

    public function testServiceWiringObserver() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>AmbiguousAliasedServices</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
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

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>CustomServiceAttribute</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
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

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>ProfileResolvedServices</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();

        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
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

        usort($actualServices, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame($container, $listener->getAnnotatedContainer());
        self::assertSame([
            $container->get(Fixtures::profileResolvedServices()->prodImplementation()->getName()),
        ], $actualServices);
    }

    public function testServiceWiringObserverByAttributesProfileAware() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>CustomServiceAttribute</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $emitter = new Emitter();
        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
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

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

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
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
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

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $containerFactory = $this->getMockBuilder(ContainerFactory::class)->getMock();
        $containerFactory->expects($this->once())
            ->method('createContainer')
            ->willReturn($container = $this->getMockBuilder(AnnotatedContainer::class)->getMock());

        $emitter = new Emitter();
        $subject = Bootstrap::fromCompleteSetup(
            $containerFactory,
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        );

        $actual = $subject->bootstrapContainer();

        self::assertSame($container, $actual);
    }

    public function testBootstrapEventsTriggeredInCorrectOrder() : void {
        $emitter = new Emitter();
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $listener = new StubBootstrapListener();
        $emitter->addListener($listener);

        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
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

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $cacheKey = CacheKey::fromContainerDefinitionAnalysisOptions(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories(
                $directoryResolver->pathFromRoot('SingleConcreteService')
            )->build()
        );
        $cache = $this->getMockBuilder(ContainerDefinitionCache::class)->getMock();
        $cache->expects($this->once())
            ->method('get')
            ->with($this->callback(fn(CacheKey $key) => $key->asString() === $cacheKey->asString()))
            ->willReturn(null);

        $bootstrap = Bootstrap::fromCompleteSetup(
            new AurynContainerFactory($emitter),
            $emitter,
            $directoryResolver,
            new DefaultParameterStoreFactory(),
            new DefaultDefinitionProviderFactory(),
            new Stopwatch()
        );
        $bootstrap->bootstrapContainer(
            bootstrappingConfigurationProvider: new CacheAwareBootstrappingConfigurationProvider(
                new XmlBootstrappingConfigurationProvider(),
                $cache
            )
        );
    }
}

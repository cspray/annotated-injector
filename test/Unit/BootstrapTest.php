<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\Bootstrap;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalyticsObserver;
use Cspray\AnnotatedContainer\Bootstrap\ContainerCreatedObserver;
use Cspray\AnnotatedContainer\Bootstrap\DefinitionProviderFactory;
use Cspray\AnnotatedContainer\Bootstrap\ObserverFactory;
use Cspray\AnnotatedContainer\Bootstrap\ParameterStoreFactory;
use Cspray\AnnotatedContainer\Bootstrap\PreAnalysisObserver;
use Cspray\AnnotatedContainer\Bootstrap\ServiceFromServiceDefinition;
use Cspray\AnnotatedContainer\Bootstrap\ServiceGatherer;
use Cspray\AnnotatedContainer\Bootstrap\ServiceWiringObserver;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Unit\Helper\FixtureBootstrappingDirectoryResolver;
use Cspray\AnnotatedContainer\Unit\Helper\StubAnalyticsObserver;
use Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapObserver;
use Cspray\AnnotatedContainer\Unit\Helper\StubDefinitionProviderWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStoreWithDependencies;
use Cspray\AnnotatedContainer\Unit\Helper\TestLogger;
use Cspray\AnnotatedContainerFixture\CustomServiceAttribute\Repository;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\PrecisionStopwatch\KnownIncrementingPreciseTime;
use Cspray\PrecisionStopwatch\Stopwatch;
use org\bovigo\vfs\vfsStream as VirtualFilesystem;
use org\bovigo\vfs\vfsStreamDirectory as VirtualDirectory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer();

        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }

    public function testBootstrapSingleConcreteServiceWithCache() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <cacheDir>.annotated-container-cache</cacheDir>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        VirtualFilesystem::newDirectory('.annotated-container-cache')
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $bootstrap->bootstrapContainer();
        $expected = md5(Fixtures::singleConcreteService()->getPath());

        self::assertFileExists('vfs://root/.annotated-container-cache/' . $expected);
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer(profiles: ['default', 'dev']);
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $container = $bootstrap->bootstrapContainer(configurationFile: 'my-container.xml.dist');

        $service = $container->get(Fixtures::singleConcreteService()->fooImplementation()->getName());

        self::assertInstanceOf(
            Fixtures::singleConcreteService()->fooImplementation()->getName(),
            $service
        );
    }

    public function testBootstrapWithLogging() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <logging>
      <file>annotated-container.log</file>
    </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $container = (new Bootstrap(directoryResolver: $directoryResolver))->bootstrapContainer();

        self::assertFileExists('vfs://root/annotated-container.log');
        $logContents = file_get_contents('vfs://root/annotated-container.log');
        self::assertStringContainsString('Annotated Container compiling started.', $logContents);
        self::assertStringContainsString(
            sprintf(
                'Started wiring AnnotatedContainer with %s backing implementation and "default" active profiles.',
                $container->getBackingContainer()::class,
            ),
            $logContents
        );
    }

    public function testBootstrapWithLoggingProfileExcluded() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <logging>
      <file>annotated-container.log</file>
      <exclude><profile>test</profile></exclude>
    </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        (new Bootstrap(directoryResolver: $directoryResolver))->bootstrapContainer(['default', 'test']);

        self::assertFileExists('vfs://root/annotated-container.log');
        $logContents = file_get_contents('vfs://root/annotated-container.log');
        self::assertSame('', $logContents);
    }

    public function testBootstrapWithLoggerPassedOverridesConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <logging>
      <file>annotated-container.log</file>
    </logging>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($xml)
            ->at($this->vfs);

        $logger = new TestLogger();
        (new Bootstrap(directoryResolver: $directoryResolver, logger: $logger))->bootstrapContainer();

        self::assertStringEqualsFile('vfs://root/annotated-container.log', '');
        self::assertGreaterThan(1, count($logger->getLogsForLevel(LogLevel::INFO)));
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

        $container = (new Bootstrap(directoryResolver: $directoryResolver, definitionProviderFactory: $factory))->bootstrapContainer();

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

        $container = (new Bootstrap(directoryResolver: $directoryResolver, parameterStoreFactory: $factory))->bootstrapContainer();

        $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());

        self::assertSame('ac-ackey', $service->key);
    }

    public function testBootstrapObserverInvokedCorrectOrder() : void {
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);

        $bootstrap->addObserver($subject = new StubBootstrapObserver());

        $bootstrap->bootstrapContainer();

        self::assertCount(3, $subject->getInvokedMethods());
        self::assertSame([
            [sprintf('%s::%s', StubBootstrapObserver::class, 'notifyPreAnalysis')],
            [sprintf('%s::%s', StubBootstrapObserver::class, 'notifyPostAnalysis')],
            [sprintf('%s::%s', StubBootstrapObserver::class, 'notifyContainerCreated')]
        ], $subject->getInvokedMethods());
    }

    public function testBootstrapMultipleObservers() : void {
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);

        $bootstrap->addObserver($one = new StubBootstrapObserver());
        $bootstrap->addObserver($two = new StubBootstrapObserver());
        $bootstrap->addObserver($three = new StubBootstrapObserver());

        $bootstrap->bootstrapContainer();

        self::assertCount(3, $one->getInvokedMethods());
        self::assertCount(3, $two->getInvokedMethods());
        self::assertCount(3, $three->getInvokedMethods());
    }

    public function testObserversAddedFromConfiguration() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <observers>
      <observer>Cspray\AnnotatedContainer\Unit\Helper\StubBootstrapObserver</observer>
    </observers>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);
        $bootstrap->bootstrapContainer();

        $observers = (new \ReflectionObject($bootstrap))->getProperty('observers')->getValue($bootstrap);

        self::assertCount(1, $observers);
        self::assertInstanceOf(StubBootstrapObserver::class, $observers[0]);
        self::assertCount(3, $observers[0]->getInvokedMethods());
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);

        $observer = new class extends ServiceWiringObserver {

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
                $this->services = $gatherer->getServicesForType(Fixtures::ambiguousAliasedServices()->fooInterface()->getName());
            }
        };
        $bootstrap->addObserver($observer);

        $container = $bootstrap->bootstrapContainer();

        $actual = $observer->getServices();

        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->getService(), $actual);

        usort($actualServices, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame($container, $observer->getAnnotatedContainer());
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);

        $observer = new class extends ServiceWiringObserver {

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
                $this->services = $gatherer->getServicesWithAttribute(Repository::class);
            }
        };
        $bootstrap->addObserver($observer);

        $container = $bootstrap->bootstrapContainer(['default', 'test']);

        $actual = $observer->getServices();
        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->getService(), $actual);

        self::assertSame($container, $observer->getAnnotatedContainer());
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);

        $observer = new class extends ServiceWiringObserver {

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
                $this->services = $gatherer->getServicesForType(Fixtures::profileResolvedServices()->fooInterface()->getName());
            }
        };
        $bootstrap->addObserver($observer);

        $container = $bootstrap->bootstrapContainer(['default', 'prod']);

        $actual = $observer->getServices();

        $actualServices = array_map(fn(ServiceFromServiceDefinition $fromServiceDefinition) => $fromServiceDefinition->getService(), $actual);

        usort($actualServices, fn($a, $b) => $a::class <=> $b::class);

        self::assertSame($container, $observer->getAnnotatedContainer());
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

        $bootstrap = new Bootstrap(directoryResolver: $directoryResolver);

        $observer = new class extends ServiceWiringObserver {

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
                $this->services = $gatherer->getServicesWithAttribute(Repository::class);
            }
        };
        $bootstrap->addObserver($observer);

        // The Repository is only active under 'test' profile and should not be included
        $container = $bootstrap->bootstrapContainer(['default', 'dev']);

        self::assertSame($container, $observer->getAnnotatedContainer());
        self::assertEmpty($observer->getServices());
    }

    public function testObserverFactoryRespected() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <observers>
      <observer>Passed to ObserverFactory</observer>
    </observers>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $observerFactory = $this->getMockBuilder(ObserverFactory::class)->getMock();
        $observerFactory->expects($this->once())
            ->method('createObserver')
            ->with('Passed to ObserverFactory')
            ->willReturn($this->getMockBuilder(PreAnalysisObserver::class)->getMock());

        (new Bootstrap(
            directoryResolver: $directoryResolver,
            observerFactory: $observerFactory
        ))->bootstrapContainer();
    }

    public function testContainerAnalyticsObserverNotifiedAfterContainerCreated() : void {
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

        $observer = new class implements ContainerCreatedObserver, ContainerAnalyticsObserver {
            private array $calls = [];

            public function notifyAnalytics(ContainerAnalytics $analytics) : void {
                $this->calls[] = __FUNCTION__;
            }

            public function notifyContainerCreated(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
                $this->calls[] = __FUNCTION__;
            }

            public function getCalls() : array {
                return $this->calls;
            }
        };

        $subject = new Bootstrap($directoryResolver);
        $subject->addObserver($observer);

        $subject->bootstrapContainer();

        self::assertSame(['notifyContainerCreated', 'notifyAnalytics'], $observer->getCalls());
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

        $observer = new class implements ContainerAnalyticsObserver {
            private ?ContainerAnalytics $analytics = null;

            public function notifyAnalytics(ContainerAnalytics $analytics) : void {
                $this->analytics = $analytics;
            }

            public function getAnalytics() : ?ContainerAnalytics {
                return $this->analytics;
            }
        };

        $subject = new Bootstrap(
            directoryResolver: $directoryResolver,
            stopwatch: new Stopwatch(new KnownIncrementingPreciseTime())
        );
        $subject->addObserver($observer);

        $subject->bootstrapContainer();

        $analytics = $observer->getAnalytics();
        self::assertNotNull($analytics);

        self::assertSame(3, $analytics->totalTime->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timePreppingForAnalysis->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timeTakenForAnalysis->timeTakenInNanoseconds());
        self::assertSame(1, $analytics->timeTakenCreatingContainer->timeTakenInNanoseconds());
    }

    public function testTimeTakenInMillisecondsLoggedIfLoggerPresent() : void {
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

        $logger = new TestLogger();
        $subject = new Bootstrap(
            directoryResolver: $directoryResolver,
            logger: $logger,
            stopwatch: new Stopwatch(new KnownIncrementingPreciseTime())
        );

        $subject->bootstrapContainer();

        $logs = $logger->getLogsForLevel(LogLevel::INFO);
        self::assertContainsEquals([
            'message' => 'Took 0.000003ms to analyze and create your container. 0.000001ms was spent preparing for analysis. 0.000001ms was spent statically analyzing your codebase. 0.000001ms was spent wiring your container.',
            'context' => [
                'total_time_in_ms' => 3.0e-6,
                'pre_analysis_time_in_ms' => 1.0e-6,
                'post_analysis_time_in_ms' => 1.0e-6,
                'container_wired_time_in_ms' => 1.0e-6
            ]
        ], $logs);
    }

    public function testContainerAnalyticsObserverInConfigurationRespected() : void {
        $directoryResolver = new FixtureBootstrappingDirectoryResolver();

        $goodXml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<annotatedContainer xmlns="https://annotated-container.cspray.io/schema/annotated-container.xsd">
    <scanDirectories>
        <source>
            <dir>SingleConcreteService</dir>
        </source>
    </scanDirectories>
    <observers>
      <observer>Cspray\AnnotatedContainer\Unit\Helper\StubAnalyticsObserver</observer>
    </observers>
</annotatedContainer>
XML;

        VirtualFilesystem::newFile('annotated-container.xml')
            ->withContent($goodXml)
            ->at($this->vfs);

        $subject = new Bootstrap(
            directoryResolver: $directoryResolver,
        );

        StubAnalyticsObserver::$analytics = null;

        $subject->bootstrapContainer();

        self::assertNotNull(StubAnalyticsObserver::$analytics);
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

        $subject = new Bootstrap(
            directoryResolver: $directoryResolver,
            containerFactory: $containerFactory
        );

        $actual = $subject->bootstrapContainer();

        self::assertSame($container, $actual);
    }
}

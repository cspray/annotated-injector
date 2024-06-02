<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use Cspray\AnnotatedContainer\Unit\Helper\TestLogger;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainerFixture;
use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\Typiphy\Internal\NamedType;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use function Cspray\AnnotatedContainer\autowiredParams;
use function Cspray\AnnotatedContainer\rawParam;
use function Cspray\AnnotatedContainer\serviceParam;
use function Cspray\Typiphy\objectType;

abstract class ContainerFactoryTestCase extends TestCase {

    private ActiveProfiles $activeProfiles;

    abstract protected function getContainerFactory(ActiveProfiles $activeProfiles) : ContainerFactory;

    abstract protected function getBackingContainerInstanceOf() : ObjectType;

    protected function supportsInjectingMultipleNamedServices() : bool {
        return true;
    }

    private function getContainerDefinitionCompiler() : ContainerDefinitionAnalyzer {
        return new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
        );
    }

    private function getContainer(
        string $dir,
        array $profiles = [],
        ParameterStore $parameterStore = null,
        LoggerInterface $logger = null
    ) : AnnotatedContainer {
        $compiler = $this->getContainerDefinitionCompiler();
        $optionsBuilder = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir);
        $containerDefinition = $compiler->analyze($optionsBuilder->build());
        if (!empty($profiles)) {
            $containerOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$profiles);
        } else {
            $containerOptions = ContainerFactoryOptionsBuilder::forActiveProfiles('default');
        }

        if ($logger !== null) {
            $containerOptions = $containerOptions->withLogger($logger);
        }

        $this->activeProfiles = $this->getMockBuilder(ActiveProfiles::class)->getMock();
        $factory = $this->getContainerFactory($this->activeProfiles);
        if ($parameterStore !== null) {
            $factory->addParameterStore($parameterStore);
        }
        return $factory->createContainer($containerDefinition, $containerOptions->build());
    }

    public function testCreateServiceNotHasThrowsException() {
        $container = $this->getContainer(Fixtures::nonAnnotatedServices()->getPath());

        self::expectException(NotFoundExceptionInterface::class);
        self::expectExceptionMessage('The service "' . Fixtures::nonAnnotatedServices()->nonAnnotatedService()->getName() . '" could not be found in this container.');
        $container->get(Fixtures::nonAnnotatedServices()->nonAnnotatedService()->getName());
    }

    public function testGetSingleConcreteService() {
        $class = Fixtures::singleConcreteService()->fooImplementation()->getName();
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());
        $subject = $container->get($class);

        self::assertInstanceOf($class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $container = $this->getContainer(Fixtures::interfacePrepareServices()->getPath());
        $subject = $container->get(Fixtures::interfacePrepareServices()->fooInterface()->getName());

        self::assertInstanceOf(Fixtures::interfacePrepareServices()->fooImplementation()->getName(), $subject);
        self::assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnContainer() {
        $container = $this->getContainer(Fixtures::injectPrepareServices()->getPath());
        $subject = $container->get(Fixtures::injectPrepareServices()->prepareInjector()->getName());

        self::assertInstanceOf(Fixtures::injectPrepareServices()->prepareInjector()->getName(), $subject);
        self::assertSame('foo', $subject->getVal());
        self::assertInstanceOf(Fixtures::injectPrepareServices()->barImplementation()->getName(), $subject->getService());
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $container = $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath());

        self::expectException(ContainerExceptionInterface::class);
        $container->get(Fixtures::ambiguousAliasedServices()->fooInterface()->getName());
    }

    public function testServiceDelegate() {
        $container = $this->getContainer(Fixtures::delegatedService()->getPath());
        $service = $container->get(Fixtures::delegatedService()->serviceInterface()->getName());

        self::assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        self::assertTrue($container->has(Fixtures::singleConcreteService()->fooImplementation()->getName()));
        self::assertFalse($container->has(Fixtures::ambiguousAliasedServices()->fooInterface()->getName()));
    }

    public function testMultipleServicesWithPrimary() {
        $container = $this->getContainer(Fixtures::primaryAliasedServices()->getPath());

        self::assertInstanceOf(Fixtures::primaryAliasedServices()->fooImplementation()->getName(), $container->get(Fixtures::primaryAliasedServices()->fooInterface()->getName()));
    }

    public function testProfileResolvedServices() {
        $container = $this->getContainer(Fixtures::profileResolvedServices()->getPath(), ['default', 'dev']);

        $instance = $container->get(Fixtures::profileResolvedServices()->fooInterface()->getName());

        self::assertNotNull($instance);
        self::assertInstanceOf(Fixtures::profileResolvedServices()->devImplementation()->getName(), $instance);
    }

    public function testCreateNamedService() {
        $container = $this->getContainer(Fixtures::namedServices()->getPath());

        self::assertTrue($container->has('foo'));

        $instance = $container->get('foo');

        self::assertNotNull($instance);
        self::assertInstanceOf(Fixtures::namedServices()->fooImplementation()->getName(), $instance);
    }

    public function testCreateInjectStringService() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        self::assertSame('foobar', $container->get(Fixtures::injectConstructorServices()->injectStringService()->getName())->val);
    }

    public function testConcreteAliasDefinitionDoesNotHaveServiceDefinition() {
        $abstractService = Fixtures::implicitAliasedServices()->fooInterface()->getName();
        $concreteService = Fixtures::implicitAliasedServices()->fooImplementation()->getName();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract($abstract = objectType($abstractService))->build()
            )
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete = objectType($concreteService))->build()
            )->build();

        $this->activeProfiles = $this->getMockBuilder(ActiveProfiles::class)->getMock();
        $this->expectException(InvalidAlias::class);
        $this->expectExceptionMessage('An AliasDefinition has a concrete type, ' . $concrete->getName() . ', that is not a registered ServiceDefinition.');
        $this->getContainerFactory($this->activeProfiles)->createContainer($containerDefinition);
    }

    public function testMultipleServicePrepare() {
        $container = $this->getContainer(Fixtures::multiplePrepareServices()->getPath());

        $subject = $container->get(Fixtures::multiplePrepareServices()->fooImplementation()->getName());

        self::assertSame('foobar', $subject->getProperty());
    }

    public function testInjectServiceObjectMethodParam() {
        $container = $this->getContainer(Fixtures::injectServiceConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectServiceConstructorServices()->serviceInjector()->getName());

        self::assertInstanceOf(Fixtures::injectServiceConstructorServices()->fooImplementation()->getName(), $subject->foo);
    }

    public function testInjectEnvMethodParam() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectConstructorServices()->injectEnvService()->getName());
        self::assertSame(getenv('USER'), $subject->user);
    }

    public function testCreateArbitraryStorePresent() {
        $parameterStore = new class implements ParameterStore {
            public function getName(): string {
                return 'test-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                return $key . '_test_store';
            }
        };
        $container = $this->getContainer(Fixtures::injectCustomStoreServices()->getPath(), parameterStore: $parameterStore);

        $subject = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());
        self::assertSame('key_test_store', $subject->key);
    }

    public function testCreateArbitraryStoreWithUnionType() {
        $parameterStore = new class implements ParameterStore {
            public function getName() : string {
                return 'union-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                $type = Fixtures::injectUnionCustomStoreServices()->fooImplementation()->getName();
                return new $type();
            }
        };

        $container = $this->getContainer(Fixtures::injectUnionCustomStoreServices()->getPath(), parameterStore: $parameterStore);
        $subject = $container->get(Fixtures::injectUnionCustomStoreServices()->unionInjector()->getName());

        self::assertInstanceOf(Fixtures::injectUnionCustomStoreServices()->fooImplementation()->getName(), $subject->fooOrBar);
    }

    public function testCreateArbitraryStoreWithIntersectType() {
        $parameterStore = new class implements ParameterStore {
            public function getName() : string {
                return 'intersect-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                $type = Fixtures::injectIntersectCustomStoreServices()->fooBarImplementation()->getName();
                return new $type();
            }
        };

        $container = $this->getContainer(Fixtures::injectIntersectCustomStoreServices()->getPath(), parameterStore: $parameterStore);
        $subject = $container->get(Fixtures::injectIntersectCustomStoreServices()->intersectInjector()->getName());

        self::assertInstanceOf(Fixtures::injectIntersectCustomStoreServices()->fooBarImplementation()->getName(), $subject->fooAndBar);
    }

    public function testCreateArbitraryStoreOnServiceNotPresent() {
        self::expectException(ParameterStoreNotFound::class);
        self::expectExceptionMessage('The ParameterStore "test-store" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.');
        $this->getContainer(Fixtures::injectCustomStoreServices()->getPath());
    }

    public function testCreateArbitraryStoreOnConfigurationNotPresent() {
        self::expectException(ParameterStoreNotFound::class);
        self::expectExceptionMessage('The ParameterStore "test-store" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.');
        $this->getContainer(Fixtures::configurationMissingStore()->getPath());
    }

    public function profilesProvider() : array {
        return [
            ['from-prod', ['default', 'prod']],
            ['from-test', ['default', 'test']],
            ['from-dev', ['default', 'dev']]
        ];
    }

    /**
     * @dataProvider profilesProvider
     */
    public function testInjectProfilesMethodParam(string $expected, array $profiles)  {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath(), $profiles);
        $subject = $container->get(Fixtures::injectConstructorServices()->injectProfilesStringService()->getName());

        self::assertSame($expected, $subject->val);
    }

    public function testConfigurationSharedInstance() {
        $container = $this->getContainer(Fixtures::configurationServices()->getPath(), ['default', 'dev']);

        self::assertSame(
            $container->get(Fixtures::configurationServices()->myConfig()->getName()),
            $container->get(Fixtures::configurationServices()->myConfig()->getName())
        );
    }

    public function testConfigurationValues() {
        $container = $this->getContainer(Fixtures::configurationServices()->getPath(), ['default', 'dev']);
        /** @var AnnotatedContainerFixture\ConfigurationServices\MyConfig $subject */
        $subject = $container->get(Fixtures::configurationServices()->myConfig()->getName());

        self::assertSame('my-api-key', $subject->key);
        self::assertSame(1234, $subject->port);
        self::assertSame(getenv('USER'), $subject->user);
        self::assertTrue($subject->testMode);
    }

    public function testNamedConfigurationInstanceOf() {
        $container = $this->getContainer(Fixtures::namedConfigurationServices()->getPath());

        self::assertInstanceOf(Fixtures::namedConfigurationServices()->myConfig()->getName(), $container->get('my-config'));
    }

    public function testMakeAutowiredObject() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->getName(), autowiredParams(rawParam('scalar', '802')));

        self::assertInstanceOf(Fixtures::autowireableFactoryServices()->fooImplementation()->getName(), $subject->foo);
        self::assertSame('802', $subject->scalar);
    }

    public function testMakeAutowiredObjectReplaceServiceTarget() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->getName(), autowiredParams(
            rawParam('scalar', 'quarters'),
            serviceParam('foo', Fixtures::autowireableFactoryServices()->barImplementation())
        ));

        self::assertInstanceOf(Fixtures::autowireableFactoryServices()->barImplementation()->getName(), $subject->foo);
        self::assertSame('quarters', $subject->scalar);
    }

    public function testBackingContainerInstanceOf() {
        $this->activeProfiles = $this->getMockBuilder(ActiveProfiles::class)->getMock();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        self::assertInstanceOf(
            $this->getBackingContainerInstanceOf()->getName(),
            $this->getContainerFactory($this->activeProfiles)->createContainer($containerDefinition)->getBackingContainer()
        );
    }

    public function testGettingAutowireableFactory() {
        $this->activeProfiles = $this->getMockBuilder(ActiveProfiles::class)->getMock();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory($this->activeProfiles)->createContainer($containerDefinition);

        self::assertSame($container, $container->get(AutowireableFactory::class));
    }

    public function testGettingAutowireableInvoker() {
        $this->activeProfiles = $this->getMockBuilder(ActiveProfiles::class)->getMock();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory($this->activeProfiles)->createContainer($containerDefinition);

        self::assertSame($container, $container->get(AutowireableInvoker::class));
    }

    public function testNamedServicesShared() : void {
        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        $namedService = $container->get('bar');
        $typedService = $container->get(Fixtures::injectNamedServices()->barImplementation()->getName());

        self::assertSame($namedService, $typedService);
    }

    public function testInjectingNamedServices() : void {
        if (!$this->supportsInjectingMultipleNamedServices()) {
            $this->markTestSkipped(
                $this->getBackingContainerInstanceOf()->getName() . ' does not support injecting multiple named services.'
            );
        }

        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        /** @var AnnotatedContainerFixture\InjectNamedServices\ServiceConsumer $service */
        $service = $container->get(Fixtures::injectNamedServices()->serviceConsumer()->getName());

        self::assertInstanceOf(Fixtures::injectNamedServices()->fooImplementation()->getName(), $service->foo);
        self::assertInstanceOf(Fixtures::injectNamedServices()->barImplementation()->getName(), $service->bar);
    }

    public function testGettingActiveProfilesImplicitlyShared() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        $a = $container->get(ActiveProfiles::class);
        $b = $container->get(ActiveProfiles::class);

        self::assertInstanceOf(ActiveProfiles::class, $a);
        self::assertSame($a, $b);
    }

    public function testGettingActiveProfilesHasCorrectList() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), ['default', 'foo', 'bar']);

        /** @var ActiveProfiles $activeProfile */
        $activeProfile = $container->get(ActiveProfiles::class);

        self::assertSame(['default', 'foo', 'bar'], $activeProfile->getProfiles());
    }

    public function testInvokeWithImplicitAlias() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $state = new \stdClass();
        $state->foo = null;
        $callable = fn(AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface $foo) => $state->foo = $foo;

        $invoker->invoke($callable);

        self::assertInstanceOf(Fixtures::implicitAliasedServices()->fooImplementation()->getName(), $state->foo);
    }

    public function testInvokeWithAmbiguousAliasRespectsParameters() : void {
        $invoker = $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath());
        $state = new \stdClass();
        $state->foo = null;
        $callable = fn(AnnotatedContainerFixture\AmbiguousAliasedServices\FooInterface $foo) => $state->foo = $foo;
        $invoker->invoke($callable, autowiredParams(serviceParam('foo', Fixtures::ambiguousAliasedServices()->quxImplementation())));

        self::assertInstanceOf(Fixtures::ambiguousAliasedServices()->quxImplementation()->getName(), $state->foo);
    }

    public function testInvokeWithScalarParameter() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $state = new \stdClass();
        $state->bar = null;
        $callable = fn(AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface $foo, string $bar) => $state->bar = $bar;

        $invoker->invoke($callable, autowiredParams(rawParam('bar', 'foobaz')));

        self::assertSame('foobaz', $state->bar);
    }

    public function testInvokeReturnsCallableReturnValue() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $callable = fn() => 'returned from fn()';

        $actual = $invoker->invoke($callable);

        self::assertSame('returned from fn()', $actual);
    }

    public function testServiceProfileNotActiveNotShared() : void {
        $container = $this->getContainer(Fixtures::profileResolvedServices()->getPath(), ['default', 'dev']);

        self::assertTrue($container->has(Fixtures::profileResolvedServices()->fooInterface()->getName()));
        self::assertTrue($container->has(Fixtures::profileResolvedServices()->devImplementation()->getName()));
        self::assertFalse($container->has(Fixtures::profileResolvedServices()->prodImplementation()->getName()));
        self::assertFalse($container->has(Fixtures::profileResolvedServices()->testImplementation()->getName()));
    }

    public function testNamedServiceProfileNotActiveNotShared() : void {
        $container = $this->getContainer(Fixtures::namedProfileResolvedServices()->getPath(), ['default', 'prod']);

        self::assertTrue($container->has(Fixtures::namedProfileResolvedServices()->fooInterface()->getName()));
        self::assertTrue($container->has('prod-foo'));
        self::assertFalse($container->has('dev-foo'));
        self::assertFalse($container->has('test-foo'));
    }

    public function deserializeContainerProvider() : array {
        return [
            [Fixtures::injectCustomStoreServices(), function(ContainerFactory $containerFactory, ContainerDefinition $deserialize) {
                $store = $this->getMockBuilder(ParameterStore::class)->getMock();
                $store->expects($this->once())
                    ->method('getName')
                    ->willReturn('test-store');

                $store->expects($this->once())
                    ->method('fetch')
                    ->with($this->isInstanceOf(NamedType::class), 'key')
                    ->willReturn('the store key value');
                $containerFactory->addParameterStore($store);

                $container = $containerFactory->createContainer($deserialize);
                $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->getName());

                self::assertSame('the store key value', $service->key);
            }],
            [Fixtures::injectConstructorServices(), function(ContainerFactory $containerFactory, ContainerDefinition $deserialize) {
                $container = $containerFactory->createContainer($deserialize);

                $service = $container->get(Fixtures::injectConstructorServices()->injectTypeUnionService()->getName());

                self::assertSame(4.20, $service->value);
            }]
        ];
    }

    /**
     * @dataProvider deserializeContainerProvider
     */
    public function testDeserializingContainerWithInjectAllowsServiceCreation(Fixture $fixture, callable $assertions) {
        $serializer = new ContainerDefinitionSerializer();
        $containerDefinition = $this->getContainerDefinitionCompiler()->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($fixture->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $deserialize = $serializer->deserialize($serialized);

        $this->activeProfiles = $this->getMockBuilder(ActiveProfiles::class)->getMock();
        $containerFactory = $this->getContainerFactory($this->activeProfiles);

        $assertions($containerFactory, $deserialize);
    }

    public function testLoggingCreatingContainerWithActiveProfiles() : void {
        $logger = new TestLogger();
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), profiles: ['default', 'foo', 'bar'], logger: $logger);

        $expected = [
            'message' => sprintf(
                'Started wiring AnnotatedContainer with %s backing implementation and "default, foo, bar" active profiles.',
                $container->getBackingContainer()::class
            ),
            'context' => [
                'backingImplementation' => $container->getBackingContainer()::class,
                'activeProfiles' => ['default', 'foo', 'bar']
            ]
        ];

        $logs = $logger->getLogsForLevel(LogLevel::INFO);
        self::assertSame($expected, $logs[0]);
    }

    public function testLoggingCreatingContainerFinished() : void {
        $logger = new TestLogger();
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), profiles: ['default', 'foo', 'bar'], logger: $logger);

        $expected = [
            'message' => 'Finished wiring AnnotatedContainer.',
            'context' => [
                'backingImplementation' => $container->getBackingContainer()::class,
                'activeProfiles' => ['default', 'foo', 'bar']
            ]
        ];

        $logs = $logger->getLogsForLevel(LogLevel::INFO);
        self::assertSame($expected, $logs[count($logs) - 1]);
    }

    public function testLoggingServiceShared() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::singleConcreteService()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf('Shared service %s.', Fixtures::singleConcreteService()->fooImplementation()->getName()),
            'context' => [
                'service' => Fixtures::singleConcreteService()->fooImplementation()->getName()
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingNamedService() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::namedServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf('Aliased name "foo" to service %s.', Fixtures::namedServices()->fooInterface()->getName()),
            'context' => [
                'service' => Fixtures::namedServices()->fooInterface()->getName(),
                'name' => 'foo'
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServiceDelegate() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::delegatedService()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Delegated construction of service %s to %s::%s.',
                Fixtures::delegatedService()->serviceInterface()->getName(),
                Fixtures::delegatedService()->serviceFactory()->getName(),
                'createService'
            ),
            'context' => [
                'service' => Fixtures::delegatedService()->serviceInterface()->getName(),
                'delegatedType' => Fixtures::delegatedService()->serviceFactory()->getName(),
                'delegatedMethod' => 'createService'
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServicePrepare() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::interfacePrepareServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Preparing service %s with method %s.',
                Fixtures::interfacePrepareServices()->fooInterface()->getName(),
                'setBar'
            ),
            'context' => [
                'service' => Fixtures::interfacePrepareServices()->fooInterface()->getName(),
                'method' => 'setBar'
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingImplicitAliasService() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::implicitAliasedServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Alias resolution attempted for abstract service %s. Found concrete service %s, because SingleConcreteService.',
                Fixtures::implicitAliasedServices()->fooInterface()->getName(),
                Fixtures::implicitAliasedServices()->fooImplementation()->getName(),
            ),
            'context' => [
                'abstractService' => Fixtures::implicitAliasedServices()->fooInterface()->getName(),
                'concreteService' => Fixtures::implicitAliasedServices()->fooImplementation()->getName(),
                'aliasingReason' => AliasResolutionReason::SingleConcreteService
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingServiceIsPrimary() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::primaryAliasedServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Alias resolution attempted for abstract service %s. Found concrete service %s, because ConcreteServiceIsPrimary.',
                Fixtures::primaryAliasedServices()->fooInterface()->getName(),
                Fixtures::primaryAliasedServices()->fooImplementation()->getName(),
            ),
            'context' => [
                'abstractService' => Fixtures::primaryAliasedServices()->fooInterface()->getName(),
                'concreteService' => Fixtures::primaryAliasedServices()->fooImplementation()->getName(),
                'aliasingReason' => AliasResolutionReason::ConcreteServiceIsPrimary
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingAliasMultipleService() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Alias resolution attempted for abstract service %s. No concrete service found, because MultipleConcreteService.',
                Fixtures::ambiguousAliasedServices()->fooInterface()->getName(),
            ),
            'context' => [
                'abstractService' => Fixtures::ambiguousAliasedServices()->fooInterface()->getName(),
                'concreteService' => null,
                'aliasingReason' => AliasResolutionReason::MultipleConcreteService
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingConfiguration() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::configurationServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Shared configuration %s.',
                Fixtures::configurationServices()->myConfig()->getName()
            ),
            'context' => [
                'configuration' => Fixtures::configurationServices()->myConfig()->getName()
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingNamedConfiguration() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::namedConfigurationServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf('Aliased name "my-config" to configuration %s.', Fixtures::namedConfigurationServices()->myConfig()->getName()),
            'context' => [
                'configuration' => Fixtures::namedConfigurationServices()->myConfig()->getName(),
                'name' => 'my-config'
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectNonServiceMethodParameterNotFromStore() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::injectConstructorServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Injecting value "42" into %s::__construct($meaningOfLife).',
                Fixtures::injectConstructorServices()->injectIntService()->getName()
            ),
            'context' => [
                'service' => Fixtures::injectConstructorServices()->injectIntService()->getName(),
                'method' => '__construct',
                'parameter' => 'meaningOfLife',
                'type' => 'int',
                'value' => 42
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectNonServiceMethodParameterFromStore() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::injectCustomStoreServices()->getPath(),
            parameterStore: new StubParameterStore(),
            logger: $logger
        );

        $expected = [
            'message' => sprintf(
                'Injecting value from test-store ParameterStore for key "key" into %s::__construct($key).',
                Fixtures::injectCustomStoreServices()->scalarInjector()->getName()
            ),
            'context' => [
                'service' => Fixtures::injectCustomStoreServices()->scalarInjector()->getName(),
                'method' => '__construct',
                'parameter' => 'key',
                'type' => 'string',
                'value' => 'key',
                'store' => 'test-store'
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectServiceMethodParameter() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::injectServiceConstructorServices()->getPath(),
            logger: $logger
        );

        $expected = [
            'message' => sprintf(
                'Injecting service %s from Container into %s::__construct($foo).',
                Fixtures::injectServiceConstructorServices()->fooImplementation()->getName(),
                Fixtures::injectServiceConstructorServices()->serviceInjector()->getName(),

            ),
            'context' => [
                'service' => Fixtures::injectServiceConstructorServices()->serviceInjector()->getName(),
                'method' => '__construct',
                'parameter' => 'foo',
                'type' => Fixtures::injectServiceConstructorServices()->fooInterface()->getName(),
                'value' => Fixtures::injectServiceConstructorServices()->fooImplementation()->getName(),
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectEnumValueMethodParameter() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::injectEnumConstructorServices()->getPath(),
            logger: $logger
        );

        $expected = [
            'message' => sprintf(
                'Injecting enum "%s::North" into %s::__construct($directions).',
                InjectEnumConstructorServices\CardinalDirections::class,
                Fixtures::injectEnumConstructorServices()->enumInjector()->getName()
            ),
            'context' => [
                'service' => Fixtures::injectEnumConstructorServices()->enumInjector()->getName(),
                'method' => '__construct',
                'parameter' => 'directions',
                'type' => InjectEnumConstructorServices\CardinalDirections::class,
                'value' => InjectEnumConstructorServices\CardinalDirections::North
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectNonServiceNotFromStoreConfigurationProperty() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::configurationServices()->getPath(),
            logger: $logger
        );

        $expected = [
            'message' => sprintf(
                'Injecting value "1234" into %s::port.',
                Fixtures::configurationServices()->myConfig()->getName()
            ),
            'context' => [
                'configuration' => Fixtures::configurationServices()->myConfig()->getName(),
                'property' => 'port',
                'type' => 'int',
                'value' => 1234
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectEnumFromConfigurationProperty() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::configurationWithEnum()->getPath(),
            logger: $logger
        );

        $expected = [
            'message' => sprintf(
                'Injecting enum "%s::Foo" into %s::enum.',
                ConfigurationWithEnum\MyEnum::class,
                Fixtures::configurationWithEnum()->configuration()->getName(),
            ),
            'context' => [
                'configuration' => Fixtures::configurationWithEnum()->configuration()->getName(),
                'property' => 'enum',
                'type' => ConfigurationWithEnum\MyEnum::class,
                'value' => ConfigurationWithEnum\MyEnum::Foo
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectValueFromStoreForConfigurationProperty() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::configurationServices()->getPath(),
            logger: $logger
        );

        $expected = [
            'message' => sprintf(
                'Injecting value from env ParameterStore for key "USER" into %s::user.',
                Fixtures::configurationServices()->myConfig()->getName()
            ),
            'context' => [
                'configuration' => Fixtures::configurationServices()->myConfig()->getName(),
                'property' => 'user',
                'type' => 'string',
                'value' => 'USER',
                'store' => 'env'
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectServiceForConfigurationProperty() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::configurationInjectServiceFixture()->getPath(),
            logger: $logger
        );
        $expected = [
            'message' => sprintf(
                'Injecting service %s from Container into %s::foo.',
                Fixtures::configurationInjectServiceFixture()->fooService()->getName(),
                Fixtures::configurationInjectServiceFixture()->fooConfig()->getName(),

            ),
            'context' => [
                'configuration' => Fixtures::configurationInjectServiceFixture()->fooConfig()->getName(),
                'property' => 'foo',
                'type' => Fixtures::configurationInjectServiceFixture()->fooService()->getName(),
                'value' => Fixtures::configurationInjectServiceFixture()->fooService()->getName(),
            ]
        ];
        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingSkippingServicesBecauseProfileMismatch() : void {
        $logger = new TestLogger();
        $this->getContainer(
            Fixtures::profileResolvedServices()->getPath(),
            profiles: ['default', 'dev'],
            logger: $logger
        );

        $expectedTest = [
            'message' => sprintf(
                'Not considering %s as shared service because profiles do not match.',
                Fixtures::profileResolvedServices()->testImplementation()->getName()
            ),
            'context' => [
                'service' => Fixtures::profileResolvedServices()->testImplementation()->getName(),
                'profiles' => ['test']
            ]
        ];
        $expectedProd = [
            'message' => sprintf(
                'Not considering %s as shared service because profiles do not match.',
                Fixtures::profileResolvedServices()->prodImplementation()->getName()
            ),
            'context' => [
                'service' => Fixtures::profileResolvedServices()->prodImplementation()->getName(),
                'profiles' => ['prod']
            ]
        ];

        self::assertContains($expectedTest, $logger->getLogsForLevel(LogLevel::INFO));
        self::assertContains($expectedProd, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectMethodArrayNotMultiline() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::injectConstructorServices()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Injecting value "[\'dependency\', \'injection\', \'rocks\']" into %s::__construct($values).',
                Fixtures::injectConstructorServices()->injectArrayService()->getName()
            ),
            'context' => [
                'service' => Fixtures::injectConstructorServices()->injectArrayService()->getName(),
                'method' => '__construct',
                'parameter' => 'values',
                'type' => 'array',
                'value' => ['dependency', 'injection', 'rocks']
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testLoggingInjectPropertyArrayNotMultiline() : void {
        $logger = new TestLogger();
        $this->getContainer(Fixtures::configurationWithArrayEnum()->getPath(), logger: $logger);

        $expected = [
            'message' => sprintf(
                'Injecting value "[%s::Bar, %s::Qux]" into %s::cases.',
                AnnotatedContainerFixture\ConfigurationWithArrayEnum\FooEnum::class,
                AnnotatedContainerFixture\ConfigurationWithArrayEnum\FooEnum::class,
                Fixtures::configurationWithArrayEnum()->myConfiguration()->getName()
            ),
            'context' => [
                'configuration' => Fixtures::configurationWithArrayEnum()->myConfiguration()->getName(),
                'property' => 'cases',
                'type' => 'array',
                'value' => [AnnotatedContainerFixture\ConfigurationWithArrayEnum\FooEnum::Bar, AnnotatedContainerFixture\ConfigurationWithArrayEnum\FooEnum::Qux]
            ]
        ];

        self::assertContains($expected, $logger->getLogsForLevel(LogLevel::INFO));
    }

    public function testCreatingConstructorPromotedConfiguration() : void {
        $container = $this->getContainer(Fixtures::constructorPromotedConfigurationFixture()->getPath());

        $configuration = $container->get(Fixtures::constructorPromotedConfigurationFixture()->constructorConfig()->getName());

        self::assertInstanceOf(Fixtures::constructorPromotedConfigurationFixture()->constructorConfig()->getName(), $configuration);
        self::assertSame('bar', $configuration->foo);
        self::assertSame(42, $configuration->bar);
    }

    public function testCreatingAliasedConfiguration() : void {
        $container = $this->getContainer(Fixtures::aliasedConfigurationFixture()->getPath());

        $configuration = $container->get(Fixtures::aliasedConfigurationFixture()->appConfig()->getName());

        self::assertInstanceOf(Fixtures::aliasedConfigurationFixture()->myAppConfig()->getName(), $configuration);
        self::assertSame('my-app-name', $configuration->getAppName());
    }

    public function testCreatingServiceWithInjectServiceCollection() : void {
        $container = $this->getContainer(Fixtures::injectServiceCollection()->getPath());

        $collectionInjector = $container->get(Fixtures::injectServiceCollection()->collectionInjector()->getName());

        self::assertCount(3, $collectionInjector->services);
        self::assertContainsOnlyInstancesOf(
            Fixtures::injectServiceCollection()->fooInterface()->getName(),
            $collectionInjector->services
        );
    }

    public function testCreatingServiceWithInjectServiceDomainCollection() : void {
        $container = $this->getContainer(Fixtures::injectServiceDomainCollection()->getPath());

        $collectionInjector = $container->get(Fixtures::injectServiceDomainCollection()->collectionInjector()->getName());

        self::assertCount(3, $collectionInjector->collection->services);
        self::assertContainsOnlyInstancesOf(
            Fixtures::injectServiceDomainCollection()->fooInterface()->getName(),
            $collectionInjector->collection->services
        );
    }
}

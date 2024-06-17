<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptionsBuilder;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\Serializer\XmlContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\Unit\Helper\StubContainerFactoryListener;
use Cspray\AnnotatedContainer\Unit\Helper\StubParameterStore;
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Cspray\AnnotatedContainer\autowiredParams;
use function Cspray\AnnotatedContainer\rawParam;
use function Cspray\AnnotatedContainer\serviceParam;
use function Cspray\Typiphy\objectType;

abstract class ContainerFactoryTestCase extends TestCase {

    abstract protected function getContainerFactory(Emitter $emitter = new Emitter()) : ContainerFactory;

    abstract protected function getBackingContainerInstanceOf() : ObjectType;

    protected function supportsInjectingMultipleNamedServices() : bool {
        return true;
    }

    private function getContainerDefinitionCompiler() : ContainerDefinitionAnalyzer {
        return new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter(),
            new Emitter()
        );
    }

    private function getContainer(
        string $dir,
        Profiles $profiles = null,
        ParameterStore $parameterStore = null,
        Emitter $emitter = new Emitter()
    ) : AnnotatedContainer {
        $compiler = $this->getContainerDefinitionCompiler();
        $optionsBuilder = ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($dir);
        $containerDefinition = $compiler->analyze($optionsBuilder->build());
        $containerOptions = ContainerFactoryOptionsBuilder::forProfiles($profiles ?? Profiles::fromList(['default']));

        $factory = $this->getContainerFactory($emitter);
        if ($parameterStore !== null) {
            $factory->addParameterStore($parameterStore);
        }
        return $factory->createContainer($containerDefinition, $containerOptions->build());
    }

    public function testCreateServiceNotHasThrowsException() {
        $container = $this->getContainer(Fixtures::nonAnnotatedServices()->getPath());

        self::expectException(NotFoundExceptionInterface::class);
        self::expectExceptionMessage('The service "' . Fixtures::nonAnnotatedServices()->nonAnnotatedService()->name() . '" could not be found in this container.');
        $container->get(Fixtures::nonAnnotatedServices()->nonAnnotatedService()->name());
    }

    public function testGetSingleConcreteService() {
        $class = Fixtures::singleConcreteService()->fooImplementation()->name();
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());
        $subject = $container->get($class);

        self::assertInstanceOf($class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $container = $this->getContainer(Fixtures::interfacePrepareServices()->getPath());
        $subject = $container->get(Fixtures::interfacePrepareServices()->fooInterface()->name());

        self::assertInstanceOf(Fixtures::interfacePrepareServices()->fooImplementation()->name(), $subject);
        self::assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnContainer() {
        $container = $this->getContainer(Fixtures::injectPrepareServices()->getPath());
        $subject = $container->get(Fixtures::injectPrepareServices()->prepareInjector()->name());

        self::assertInstanceOf(Fixtures::injectPrepareServices()->prepareInjector()->name(), $subject);
        self::assertSame('foo', $subject->getVal());
        self::assertInstanceOf(Fixtures::injectPrepareServices()->barImplementation()->name(), $subject->getService());
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $container = $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath());

        self::expectException(ContainerExceptionInterface::class);
        $container->get(Fixtures::ambiguousAliasedServices()->fooInterface()->name());
    }

    public function testServiceDelegateOnInstanceMethod() : void {
        $container = $this->getContainer(Fixtures::delegatedService()->getPath());
        $service = $container->get(Fixtures::delegatedService()->serviceInterface()->name());

        self::assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testServiceDelegateOnStaticMethod() : void {
        $container = $this->getContainer(Fixtures::delegatedServiceStaticFactory()->getPath());
        $service = $container->get(Fixtures::delegatedServiceStaticFactory()->serviceInterface()->name());

        self::assertSame('From static ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        self::assertTrue($container->has(Fixtures::singleConcreteService()->fooImplementation()->name()));
        self::assertFalse($container->has(Fixtures::ambiguousAliasedServices()->fooInterface()->name()));
    }

    public function testMultipleServicesWithPrimary() {
        $container = $this->getContainer(Fixtures::primaryAliasedServices()->getPath());

        self::assertInstanceOf(Fixtures::primaryAliasedServices()->fooImplementation()->name(), $container->get(Fixtures::primaryAliasedServices()->fooInterface()->name()));
    }

    public function testProfileResolvedServices() {
        $container = $this->getContainer(Fixtures::profileResolvedServices()->getPath(), Profiles::fromList(['default', 'dev']));

        $instance = $container->get(Fixtures::profileResolvedServices()->fooInterface()->name());

        self::assertNotNull($instance);
        self::assertInstanceOf(Fixtures::profileResolvedServices()->devImplementation()->name(), $instance);
    }

    public function testCreateNamedService() {
        $container = $this->getContainer(Fixtures::namedServices()->getPath());

        self::assertTrue($container->has('foo'));

        $instance = $container->get('foo');

        self::assertNotNull($instance);
        self::assertInstanceOf(Fixtures::namedServices()->fooImplementation()->name(), $instance);
    }

    public function testCreateInjectStringService() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        self::assertSame('foobar', $container->get(Fixtures::injectConstructorServices()->injectStringService()->name())->val);
    }

    public function testConcreteAliasDefinitionDoesNotHaveServiceDefinition() {
        $abstractService = Fixtures::implicitAliasedServices()->fooInterface()->name();
        $concreteService = Fixtures::implicitAliasedServices()->fooImplementation()->name();
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract($abstract = objectType($abstractService))->build()
            )
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete = objectType($concreteService))->build()
            )->build();

        $this->expectException(InvalidAlias::class);
        $this->expectExceptionMessage('An AliasDefinition has a concrete type, ' . $concrete->name() . ', that is not a registered ServiceDefinition.');
        $this->getContainerFactory()->createContainer($containerDefinition);
    }

    public function testMultipleServicePrepare() {
        $container = $this->getContainer(Fixtures::multiplePrepareServices()->getPath());

        $subject = $container->get(Fixtures::multiplePrepareServices()->fooImplementation()->name());

        self::assertSame('foobar', $subject->getProperty());
    }

    public function testInjectServiceObjectMethodParam() {
        $container = $this->getContainer(Fixtures::injectServiceConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectServiceConstructorServices()->serviceInjector()->name());

        self::assertInstanceOf(Fixtures::injectServiceConstructorServices()->fooImplementation()->name(), $subject->foo);
    }

    public function testInjectEnvMethodParam() {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath());

        $subject = $container->get(Fixtures::injectConstructorServices()->injectEnvService()->name());
        self::assertSame(getenv('USER'), $subject->user);
    }

    public function testCreateArbitraryStorePresent() {
        $parameterStore = new class implements ParameterStore {
            public function name(): string {
                return 'test-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                return $key . '_test_store';
            }
        };
        $container = $this->getContainer(Fixtures::injectCustomStoreServices()->getPath(), parameterStore: $parameterStore);

        $subject = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->name());
        self::assertSame('key_test_store', $subject->key);
    }

    public function testCreateArbitraryStoreWithUnionType() {
        $parameterStore = new class implements ParameterStore {
            public function name() : string {
                return 'union-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                $type = Fixtures::injectUnionCustomStoreServices()->fooImplementation()->name();
                return new $type();
            }
        };

        $container = $this->getContainer(Fixtures::injectUnionCustomStoreServices()->getPath(), parameterStore: $parameterStore);
        $subject = $container->get(Fixtures::injectUnionCustomStoreServices()->unionInjector()->name());

        self::assertInstanceOf(Fixtures::injectUnionCustomStoreServices()->fooImplementation()->name(), $subject->fooOrBar);
    }

    public function testCreateArbitraryStoreWithIntersectType() {
        $parameterStore = new class implements ParameterStore {
            public function name() : string {
                return 'intersect-store';
            }

            public function fetch(Type|TypeUnion|TypeIntersect $type, string $key) : mixed {
                $type = Fixtures::injectIntersectCustomStoreServices()->fooBarImplementation()->name();
                return new $type();
            }
        };

        $container = $this->getContainer(Fixtures::injectIntersectCustomStoreServices()->getPath(), parameterStore: $parameterStore);
        $subject = $container->get(Fixtures::injectIntersectCustomStoreServices()->intersectInjector()->name());

        self::assertInstanceOf(Fixtures::injectIntersectCustomStoreServices()->fooBarImplementation()->name(), $subject->fooAndBar);
    }

    public function testCreateArbitraryStoreOnServiceNotPresent() {
        self::expectException(ParameterStoreNotFound::class);
        self::expectExceptionMessage('The ParameterStore "test-store" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.');
        $this->getContainer(Fixtures::injectCustomStoreServices()->getPath());
    }

    public static function profilesProvider() : array {
        return [
            ['from-prod', Profiles::fromList(['default', 'prod'])],
            ['from-test', Profiles::fromList(['default', 'test'])],
            ['from-dev', Profiles::fromList(['default', 'dev'])],
        ];
    }

    #[DataProvider('profilesProvider')]
    public function testInjectProfilesMethodParam(string $expected, Profiles $profiles)  {
        $container = $this->getContainer(Fixtures::injectConstructorServices()->getPath(), $profiles);
        $subject = $container->get(Fixtures::injectConstructorServices()->injectProfilesStringService()->name());

        self::assertSame($expected, $subject->val);
    }

    public function testMakeAutowiredObject() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->name(), autowiredParams(rawParam('scalar', '802')));

        self::assertInstanceOf(Fixtures::autowireableFactoryServices()->fooImplementation()->name(), $subject->foo);
        self::assertSame('802', $subject->scalar);
    }

    public function testMakeAutowiredObjectReplaceServiceTarget() {
        $container = $this->getContainer(Fixtures::autowireableFactoryServices()->getPath());
        $subject = $container->make(Fixtures::autowireableFactoryServices()->factoryCreatedService()->name(), autowiredParams(
            rawParam('scalar', 'quarters'),
            serviceParam('foo', Fixtures::autowireableFactoryServices()->barImplementation())
        ));

        self::assertInstanceOf(Fixtures::autowireableFactoryServices()->barImplementation()->name(), $subject->foo);
        self::assertSame('quarters', $subject->scalar);
    }

    public function testBackingContainerInstanceOf() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        self::assertInstanceOf(
            $this->getBackingContainerInstanceOf()->name(),
            $this->getContainerFactory()->createContainer($containerDefinition)->backingContainer()
        );
    }

    public function testGettingAutowireableFactory() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory()->createContainer($containerDefinition);

        self::assertSame($container, $container->get(AutowireableFactory::class));
    }

    public function testGettingAutowireableInvoker() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory()->createContainer($containerDefinition);

        self::assertSame($container, $container->get(AutowireableInvoker::class));
    }

    public function testNamedServicesShared() : void {
        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        $namedService = $container->get('bar');
        $typedService = $container->get(Fixtures::injectNamedServices()->barImplementation()->name());

        self::assertSame($namedService, $typedService);
    }

    public function testInjectingNamedServices() : void {
        if (!$this->supportsInjectingMultipleNamedServices()) {
            $this->markTestSkipped(
                $this->getBackingContainerInstanceOf()->name() . ' does not support injecting multiple named services.'
            );
        }

        $container = $this->getContainer(Fixtures::injectNamedServices()->getPath());

        /** @var \Cspray\AnnotatedContainer\Fixture\InjectNamedServices\ServiceConsumer $service */
        $service = $container->get(Fixtures::injectNamedServices()->serviceConsumer()->name());

        self::assertInstanceOf(Fixtures::injectNamedServices()->fooImplementation()->name(), $service->foo);
        self::assertInstanceOf(Fixtures::injectNamedServices()->barImplementation()->name(), $service->bar);
    }

    public function testGettingProfilesImplicitlyShared() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath());

        $a = $container->get(Profiles::class);
        $b = $container->get(Profiles::class);

        self::assertInstanceOf(Profiles::class, $a);
        self::assertSame($a, $b);
    }

    public function testGettingProfilesHasCorrectList() : void {
        $container = $this->getContainer(Fixtures::singleConcreteService()->getPath(), Profiles::fromList(['default', 'foo', 'bar']));

        $activeProfile = $container->get(Profiles::class);

        self::assertInstanceOf(Profiles::class, $activeProfile);
        self::assertSame(['default', 'foo', 'bar'], $activeProfile->toArray());
    }

    public function testInvokeWithImplicitAlias() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $state = new \stdClass();
        $state->foo = null;
        $callable = fn(\Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooInterface $foo) => $state->foo = $foo;

        $invoker->invoke($callable);

        self::assertInstanceOf(Fixtures::implicitAliasedServices()->fooImplementation()->name(), $state->foo);
    }

    public function testInvokeWithAmbiguousAliasRespectsParameters() : void {
        $invoker = $this->getContainer(Fixtures::ambiguousAliasedServices()->getPath());
        $state = new \stdClass();
        $state->foo = null;
        $callable = fn(\Cspray\AnnotatedContainer\Fixture\AmbiguousAliasedServices\FooInterface $foo) => $state->foo = $foo;
        $invoker->invoke($callable, autowiredParams(serviceParam('foo', Fixtures::ambiguousAliasedServices()->quxImplementation())));

        self::assertInstanceOf(Fixtures::ambiguousAliasedServices()->quxImplementation()->name(), $state->foo);
    }

    public function testInvokeWithScalarParameter() : void {
        $invoker = $this->getContainer(Fixtures::implicitAliasedServices()->getPath());
        $state = new \stdClass();
        $state->bar = null;
        $callable = fn(\Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooInterface $foo, string $bar) => $state->bar = $bar;

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
        $container = $this->getContainer(Fixtures::profileResolvedServices()->getPath(), Profiles::fromList(['default', 'dev']));

        self::assertTrue($container->has(Fixtures::profileResolvedServices()->fooInterface()->name()));
        self::assertTrue($container->has(Fixtures::profileResolvedServices()->devImplementation()->name()));
        self::assertFalse($container->has(Fixtures::profileResolvedServices()->prodImplementation()->name()));
        self::assertFalse($container->has(Fixtures::profileResolvedServices()->testImplementation()->name()));
    }

    public function testNamedServiceProfileNotActiveNotShared() : void {
        $container = $this->getContainer(Fixtures::namedProfileResolvedServices()->getPath(), Profiles::fromList(['default', 'prod']));

        self::assertTrue($container->has(Fixtures::namedProfileResolvedServices()->fooInterface()->name()));
        self::assertTrue($container->has('prod-foo'));
        self::assertFalse($container->has('dev-foo'));
        self::assertFalse($container->has('test-foo'));
    }

    public static function deserializeContainerProvider() : array {
        return [
            [Fixtures::injectCustomStoreServices(), function(ContainerFactory $containerFactory, ContainerDefinition $deserialize) {
                $store = new StubParameterStore();
                $containerFactory->addParameterStore($store);

                $container = $containerFactory->createContainer($deserialize);
                $service = $container->get(Fixtures::injectCustomStoreServices()->scalarInjector()->name());

                self::assertSame('from test-store key', $service->key);
            }],
            [Fixtures::injectConstructorServices(), function(ContainerFactory $containerFactory, ContainerDefinition $deserialize) {
                $container = $containerFactory->createContainer($deserialize);

                $service = $container->get(Fixtures::injectConstructorServices()->injectTypeUnionService()->name());

                self::assertSame(4.20, $service->value);
            }]
        ];
    }

    #[DataProvider('deserializeContainerProvider')]
    public function testDeserializingContainerWithInjectAllowsServiceCreation(Fixture $fixture, callable $assertions) {
        $serializer = new XmlContainerDefinitionSerializer();
        $containerDefinition = $this->getContainerDefinitionCompiler()->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($fixture->getPath())->build()
        );

        $serialized = $serializer->serialize($containerDefinition);
        $deserialize = $serializer->deserialize($serialized);

        $containerFactory = $this->getContainerFactory();

        $assertions($containerFactory, $deserialize);
    }

    public function testContainerCreationEventsEmitted() : void {
        $emitter = new Emitter();

        $listener = new StubContainerFactoryListener();
        $emitter->addListener($listener);

        $this->getContainer(
            Fixtures::singleConcreteService()->getPath(),
            Profiles::fromList(['default']),
            emitter: $emitter
        );

        self::assertSame(['BeforeContainerCreation', 'AfterContainerCreation'], $listener->getTriggeredEvents());
    }

    public function testCreatingServiceWithInjectServiceCollectionDecorator() : void {
        $container = $this->getContainer(Fixtures::injectServiceCollectionDecorator()->getPath());

        $fooService = $container->get(Fixtures::injectServiceCollectionDecorator()->fooService()->name());

        self::assertInstanceOf(
            Fixtures::injectServiceCollectionDecorator()->fooService()->name(),
            $fooService
        );
        self::assertInstanceOf(
            Fixtures::injectServiceCollectionDecorator()->compositeFoo()->name(),
            $fooService->foo
        );
        self::assertCount(3, $fooService->foo->foos);
        $fooClasses = array_map(static fn(object $foo) => $foo::class, $fooService->foo->foos);
        self::assertContains(
            Fixtures::injectServiceCollectionDecorator()->fooImplementation()->name(),
            $fooClasses
        );
        self::assertContains(
            Fixtures::injectServiceCollectionDecorator()->barImplementation()->name(),
            $fooClasses
        );
        self::assertContains(
            Fixtures::injectServiceCollectionDecorator()->bazImplementation()->name(),
            $fooClasses
        );
    }

    public function testCreatingServiceWithInjectServiceCollection() : void {
        $container = $this->getContainer(Fixtures::injectServiceCollection()->getPath());

        $collectionInjector = $container->get(Fixtures::injectServiceCollection()->collectionInjector()->name());

        self::assertCount(3, $collectionInjector->services);
        self::assertContainsOnlyInstancesOf(
            Fixtures::injectServiceCollection()->fooInterface()->name(),
            $collectionInjector->services
        );
    }

    public function testCreatingServiceWithInjectServiceDomainCollection() : void {
        $container = $this->getContainer(Fixtures::injectServiceDomainCollection()->getPath());

        $collectionInjector = $container->get(Fixtures::injectServiceDomainCollection()->collectionInjector()->name());

        self::assertCount(3, $collectionInjector->collection->services);
        self::assertContainsOnlyInstancesOf(
            Fixtures::injectServiceDomainCollection()->fooInterface()->name(),
            $collectionInjector->collection->services
        );
    }
}

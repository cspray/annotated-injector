<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\DummyApps\DummyAppUtils;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\InvalidParameterException;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Cspray\Typiphy\objectType;

abstract class ContainerFactoryTestCase extends TestCase {
    abstract protected function getContainerFactory() : ContainerFactory;

    abstract protected function getBackingContainerInstanceOf() : ObjectType;

    private function getContainerDefinitionCompiler() : ContainerDefinitionCompiler {
        return new AnnotatedTargetContainerDefinitionCompiler(
            new StaticAnalysisAnnotatedTargetParser(),
            new DefaultAnnotatedTargetDefinitionConverter()
        );
    }

    private function getContainer(string $dir, array $profiles = [], ParameterStore $parameterStore = null) : ContainerInterface&AutowireableFactory {
        $compiler = $this->getContainerDefinitionCompiler();
        $optionsBuilder = ContainerDefinitionCompileOptionsBuilder::scanDirectories($dir);
        $containerDefinition = $compiler->compile($optionsBuilder->build());
        $containerOptions = null;
        if (!empty($profiles)) {
            $containerOptions = ContainerFactoryOptionsBuilder::forActiveProfiles(...$profiles)->build();
        }
        $factory = $this->getContainerFactory();
        if (!is_null($parameterStore)) {
            $factory->addParameterStore($parameterStore);
        }
        return $factory->createContainer($containerDefinition, $containerOptions);
    }

    public function testCreateServiceNotHasThrowsException() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/SimpleServicesSomeNotAnnotated');

        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('The service "' . DummyApps\SimpleServicesSomeNotAnnotated\NotAnnotatedBarImplementation::class . '" could not be found in this container.');
        $container->get(DummyApps\SimpleServicesSomeNotAnnotated\NotAnnotatedBarImplementation::class);
    }

    public function testCreateSimpleServices() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/SimpleServices');
        $subject = $container->get(DummyApps\SimpleServices\FooInterface::class);

        $this->assertInstanceOf(DummyApps\SimpleServices\FooImplementation::class, $subject);
    }

    public function testInterfaceServicePrepare() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InterfaceServicePrepare');
        $subject = $container->get(DummyApps\InterfaceServicePrepare\FooInterface::class);

        $this->assertInstanceOf(DummyApps\InterfaceServicePrepare\FooImplementation::class, $subject);
        $this->assertEquals(1, $subject->getBarCounter());
    }

    public function testServicePrepareInvokedOnContainer() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectorExecuteServicePrepare');
        $subject = $container->get(DummyApps\InjectorExecuteServicePrepare\FooInterface::class);

        $this->assertInstanceOf(DummyApps\InjectorExecuteServicePrepare\FooImplementation::class, $subject);
        $this->assertInstanceOf(DummyApps\InjectorExecuteServicePrepare\BarImplementation::class, $subject->getBar());
    }

    public function testMultipleAliasResolutionNoMakeDefine() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/MultipleAliasResolution');

        $this->expectException(ContainerExceptionInterface::class);
        $container->get(DummyApps\MultipleAliasResolution\FooInterface::class);
    }

    public function testServiceDelegate() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/ServiceDelegate');
        $service = $container->get(DummyApps\ServiceDelegate\ServiceInterface::class);

        $this->assertSame('From ServiceFactory From FooService', $service->getValue());
    }

    public function testHasServiceIfCompiled() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/SimpleServices');

        $this->assertTrue($container->has(DummyApps\SimpleServices\FooInterface::class));
        $this->assertFalse($container->has(DummyApps\MultipleSimpleServices\FooInterface::class));
    }

    public function testMultipleServicesWithPrimary() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/MultipleServicesWithPrimary');

        $this->assertInstanceOf(DummyApps\MultipleServicesWithPrimary\FooImplementation::class, $container->get(DummyApps\MultipleServicesWithPrimary\FooInterface::class));
    }

    public function testProfileResolvedServices() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/ProfileResolvedServices', ['default', 'dev']);

        $instance = $container->get(DummyApps\ProfileResolvedServices\FooInterface::class);

        $this->assertNotNull($instance);
        $this->assertInstanceOf(DummyApps\ProfileResolvedServices\DevFooImplementation::class, $instance);
    }

    public function testCreateNamedService() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/NamedService');

        $this->assertTrue($container->has('foo'));

        $instance = $container->get('foo');

        $this->assertNotNull($instance);
        $this->assertInstanceOf(DummyApps\NamedService\FooImplementation::class, $instance);
    }

    public function testCreateInjectStringService() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectStringMethodParam');

        $this->assertSame('foobar', $container->get(DummyApps\InjectStringMethodParam\FooImplementation::class)->getParameter());
    }

    public function testCreateMultipleInjectScalarService() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectMultipleScalarMethodParam');

        /** @var DummyApps\InjectMultipleScalarMethodParam\FooImplementation $subject */
        $subject = $container->get(DummyApps\InjectMultipleScalarMethodParam\FooImplementation::class);

        $this->assertSame('foobar', $subject->getString());
        $this->assertSame(42, $subject->getInt());
        $this->assertSame(['a', 'b', 'c'], $subject->getArray());
    }

    public function testCreateInjectServicePrepare() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectIntMethodParam');

        /** @var DummyApps\InjectIntMethodParam\FooImplementation $subject */
        $subject = $container->get(DummyApps\InjectIntMethodParam\FooImplementation::class);

        $this->assertSame(42, $subject->getValue());
    }

    public function testCreateInjectScalarConstructServicePrepare() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectScalarConstructServicePrepareMethodParam');

        /** @var DummyApps\InjectScalarConstructServicePrepareMethodParam\FooImplementation $subject */
        $subject = $container->get(DummyApps\InjectScalarConstructServicePrepareMethodParam\FooImplementation::class);

        $this->assertSame('foobar', $subject->getValue());
    }

    public function testConcreteAliasDefinitionDoesNotHaveServiceDefinition() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract($abstract = objectType(DummyApps\SimpleServices\FooInterface::class))->build()
            )
            ->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete = objectType(DummyApps\SimpleServices\FooImplementation::class))->build()
            )->build();

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('An AliasDefinition is defined with a concrete type ' . $concrete->getName() . ' that is not a registered #[Service].');
        $this->getContainerFactory()->createContainer($containerDefinition);
    }

    public function testMultipleServicePrepare() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectScalarMultipleServicePrepareMethodParam');

        $subject = $container->get(DummyApps\InjectScalarMultipleServicePrepareMethodParam\FooImplementation::class);

        $this->assertSame('foobar', $subject->getValue());
    }

    public function testInjectServiceObjectMethodParam() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectServiceMethodParam');

        $subject = $container->get(DummyApps\InjectServiceMethodParam\ServiceInjector::class);

        $this->assertInstanceOf(DummyApps\InjectServiceMethodParam\FooImplementation::class, $subject->getWidget());
    }

    public function testInjectEnvMethodParam() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectEnvMethodParam');

        $subject = $container->get(DummyApps\InjectEnvMethodParam\FooImplementation::class);
        $this->assertSame(getenv('USER'), $subject->getStringParam());
    }

    public function testCreateArbitraryStorePresent() {
        $parameterStore = new class implements ParameterStore {

            public function getName(): string {
                return 'test-store';
            }

            public function fetch(Type $type, string $key): mixed {
                return $key . '_test_store';
            }
        };
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectTestStoreMethodParam', parameterStore: $parameterStore);

        $subject = $container->get(DummyApps\InjectTestStoreMethodParam\FooImplementation::class);
        $this->assertSame('key_test_store', $subject->getValue());
    }

    public function testCreateArbitraryStoreNotPresent() {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('The ParameterStore "test-store" has not been added to this ContainerFactory. Please add it with ContainerFactory::addParameterStore before creating the container.');
        $this->getContainer(DummyAppUtils::getRootDir() . '/InjectTestStoreMethodParam');
    }

    public function profilesProvider() : array {
        return [
            ['from-prod', ['prod']],
            ['from-test', ['test']],
            ['from-dev', ['dev']]
        ];
    }

    /**
     * @dataProvider profilesProvider
     */
    public function testInjectProfilesMethodParam(string $expected, array $profiles)  {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/InjectMultipleProfilesMethodParam', $profiles);
        $subject = $container->get(DummyApps\InjectMultipleProfilesMethodParam\FooImplementation::class);

        $this->assertSame($expected, $subject->getValue());
    }

    public function testConfigurationSharedInstance() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/SimpleConfiguration', ['default', 'dev']);

        $this->assertSame(
            $container->get(DummyApps\SimpleConfiguration\MyConfig::class),
            $container->get(DummyApps\SimpleConfiguration\MyConfig::class)
        );
    }

    public function testConfigurationValues() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/SimpleConfiguration', ['default', 'dev']);
        /** @var DummyApps\SimpleConfiguration\MyConfig $subject */
        $subject = $container->get(DummyApps\SimpleConfiguration\MyConfig::class);

        $this->assertSame('my-api-key', $subject->key);
        $this->assertSame(1234, $subject->port);
        $this->assertSame(getenv('USER'), $subject->user);
        $this->assertTrue($subject->testMode);
    }

    public function testNamedConfigurationInstanceOf() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/SimpleNamedConfiguration');

        $this->assertInstanceOf(DummyApps\SimpleNamedConfiguration\MyConfig::class, $container->get('my-config'));
    }

    public function testMakeAutowiredObject() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/AutowireableFactory');
        $subject = $container->make(DummyApps\AutowireableFactory\ConstructionTarget::class, autowiredParams(rawParam('value', '802')));

        $this->assertInstanceOf(DummyApps\AutowireableFactory\ServiceTargetImplementation::class, $subject->serviceTarget);
        $this->assertSame('802', $subject->value);
    }

    public function testMakeAutowiredObjectReplaceServiceTarget() {
        $container = $this->getContainer(DummyAppUtils::getRootDir() . '/AutowireableFactory');
        $subject = $container->make(DummyApps\AutowireableFactory\ConstructionTarget::class, autowiredParams(
            rawParam('value', 'quarters'),
            serviceParam('serviceTarget', objectType(DummyApps\AutowireableFactory\NotServiceTarget::class))
        ));

        $this->assertInstanceOf(DummyApps\AutowireableFactory\NotServiceTarget::class, $subject->serviceTarget);
        $this->assertSame('quarters', $subject->value);
    }

    public function testBackingContainerInstanceOf() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $this->assertInstanceOf($this->getBackingContainerInstanceOf()->getName(), $this->getContainerFactory()->createContainer($containerDefinition)->getBackingContainer());
    }

    public function testGettingAutowireableFactory() {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()->build();
        $container = $this->getContainerFactory()->createContainer($containerDefinition);

        $this->assertSame($container, $container->get(AutowireableFactory::class));
    }

}
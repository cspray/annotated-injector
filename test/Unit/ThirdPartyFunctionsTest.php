<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\AnnotatedContainer\alias;
use function Cspray\AnnotatedContainer\inject;
use function Cspray\AnnotatedContainer\serviceDelegate;
use function Cspray\AnnotatedContainer\servicePrepare;
use function Cspray\Typiphy\intType;
use function Cspray\AnnotatedContainer\service;

class ThirdPartyFunctionsTest extends TestCase {

    use ContainerDefinitionAssertionsTrait;

    public function testHasServiceDefinitionForType() : void {
        $type = Fixtures::singleConcreteService()->fooImplementation();
        $serviceDefinition = service($type);

        self::assertSame(
            $serviceDefinition->type(),
            $type
        );
    }

    public function testAbstractDefinedServiceIsAbstract() {
        $serviceDefinition = service(Fixtures::implicitAliasedServices()->fooInterface());

        self::assertTrue($serviceDefinition->isAbstract());
    }

    public function testAbstractDefinedServiceGetName() {
        $serviceDefinition = service(Fixtures::implicitAliasedServices()->fooInterface(), 'fooService');

        $this->assertSame('fooService', $serviceDefinition->name());
    }

    public function testAbstractDefinedServiceGetProfiles() {
        $serviceDefinition = service(Fixtures::implicitAliasedServices()->fooInterface(), profiles: ['default', 'dev']);

        $this->assertSame(['default', 'dev'], $serviceDefinition->profiles());
    }

    public function testSingleConcreteServiceIsConcrete() {
        $serviceDefinition = service(Fixtures::singleConcreteService()->fooImplementation());

        $this->assertTrue($serviceDefinition->isConcrete());
    }

    public function testSingleConcreteServiceIsPrimary() {
        $serviceDefinition = service(Fixtures::singleConcreteService()->fooImplementation(), isPrimary: true);

        $this->assertTrue($serviceDefinition->isPrimary());
    }

    public function testServiceDelegateDefinition() {
        $service = Fixtures::delegatedService()->serviceInterface();
        $serviceDelegateDefinition = serviceDelegate($service, Fixtures::delegatedService()->serviceFactory(), 'createService');

        $this->assertSame(Fixtures::delegatedService()->serviceInterface()->getName(), $serviceDelegateDefinition->serviceType()->getName());
        $this->assertSame(Fixtures::delegatedService()->serviceFactory()->getName(), $serviceDelegateDefinition->delegateType()->getName());
        $this->assertSame('createService', $serviceDelegateDefinition->delegateMethod());
    }

    public function testServicePrepareDefinition() {
        $servicePrepareDefinition = servicePrepare(Fixtures::interfacePrepareServices()->fooInterface(), 'setBar');

        $this->assertServicePrepareTypes([
            [Fixtures::interfacePrepareServices()->fooInterface()->getName(), 'setBar']
        ], [$servicePrepareDefinition]);
    }

    public function testInjectMethodParam() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            intType(),
            42
        );

        $this->assertSame(Fixtures::injectConstructorServices()->injectFloatService(), $inject->class());
        $this->assertSame('__construct', $inject->methodName());
        $this->assertSame('dessert', $inject->parameterName());
        $this->assertSame(intType(), $inject->type());
        $this->assertSame(42, $inject->value());
        $this->assertSame(['default'], $inject->profiles());
        $this->assertNull($inject->storeName());
    }

    public function testInjectMethodParamProfiles() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            intType(),
            42,
            ['foo', 'bar', 'baz']
        );

        $this->assertSame(['foo', 'bar', 'baz'], $inject->profiles());
    }

    public function testInjectMethodParamStoreName() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            intType(),
            42,
            from: 'store-name'
        );

        $this->assertSame('store-name', $inject->storeName());
    }
}

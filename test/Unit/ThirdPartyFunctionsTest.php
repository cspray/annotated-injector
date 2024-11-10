<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Fixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\AnnotatedContainer\Definition\inject;
use function Cspray\AnnotatedContainer\Definition\serviceDelegate;
use function Cspray\AnnotatedContainer\Definition\servicePrepare;
use function Cspray\AnnotatedContainer\Definition\service;
use function Cspray\AnnotatedContainer\Reflection\types;

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
        $serviceDelegateDefinition = serviceDelegate(Fixtures::delegatedService()->serviceFactory(), 'createService');

        $this->assertSame(Fixtures::delegatedService()->serviceInterface()->name(), $serviceDelegateDefinition->serviceType()->name());
        $this->assertSame(Fixtures::delegatedService()->serviceFactory()->name(), $serviceDelegateDefinition->delegateType()->name());
        $this->assertSame('createService', $serviceDelegateDefinition->delegateMethod());
    }

    public function testServicePrepareDefinition() {
        $servicePrepareDefinition = servicePrepare(Fixtures::interfacePrepareServices()->fooInterface(), 'setBar');

        $this->assertServicePrepareTypes([
            [Fixtures::interfacePrepareServices()->fooInterface()->name(), 'setBar']
        ], [$servicePrepareDefinition]);
    }

    public function testInjectMethodParam() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            types()->int(),
            42
        );

        $this->assertSame(Fixtures::injectConstructorServices()->injectFloatService(), $inject->class());
        $this->assertSame('__construct', $inject->methodName());
        $this->assertSame('dessert', $inject->parameterName());
        $this->assertSame(types()->int(), $inject->type());
        $this->assertSame(42, $inject->value());
        $this->assertSame(['default'], $inject->profiles());
        $this->assertNull($inject->storeName());
    }

    public function testInjectMethodParamProfiles() {
        $inject = inject(
            Fixtures::injectConstructorServices()->injectFloatService(),
            '__construct',
            'dessert',
            types()->int(),
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
            types()->int(),
            42,
            from: 'store-name'
        );

        $this->assertSame('store-name', $inject->storeName());
    }
}

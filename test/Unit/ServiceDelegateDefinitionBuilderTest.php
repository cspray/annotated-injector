<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegateDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

class ServiceDelegateDefinitionBuilderTest extends TestCase {

    public function testWithEmptyDelegateMethodThrowsException() {
        $this->expectException(InvalidServiceDelegateDefinition::class);
        $this->expectExceptionMessage('The delegate method for a ServiceDelegateDefinition must not be blank.');
        ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), '');
    }

    public function testWithDelegateMethodImmutableBuilder() {
        $builder1 = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface());
        $builder2 = $builder1->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService');

        $this->assertNotSame($builder1, $builder2);
    }

    public function testWithAttributeImmutableBuilder() {
        $builder1 = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface());
        $builder2 = $builder1->withAttribute(new ServiceDelegate());

        self::assertNotSame($builder1, $builder2);
    }

    public function testBuildHasServiceDefinition() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();

        $this->assertSame(Fixtures::delegatedService()->serviceInterface(), $delegateDefinition->serviceType());
    }

    public function testBuildHasDelegateType() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();

        $this->assertSame(Fixtures::delegatedService()->serviceFactory(), $delegateDefinition->delegateType());
    }

    public function testBuildHasDelegateMethod() {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();

        $this->assertSame('createService', $delegateDefinition->delegateMethod());
    }

    public function testBuildWithNoAttributeReturnsNull() : void {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->build();

        self::assertNull($delegateDefinition->attribute());
    }

    public function testBuildWithAttributeReturnsSameInstance() : void {
        $delegateDefinition = ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
            ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
            ->withAttribute($attr = new ServiceDelegate())
            ->build();

        self::assertSame($attr, $delegateDefinition->attribute());
    }
}

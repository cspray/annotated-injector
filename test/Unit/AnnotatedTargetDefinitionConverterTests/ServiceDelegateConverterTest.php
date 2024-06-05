<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use ReflectionMethod;

class ServiceDelegateConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::ServiceDelegate, new ReflectionMethod(
            Fixtures::delegatedService()->serviceFactory()->getName(),
            'createService'
        ));
    }

    public function testGetServiceDelegateDefinitionInstance() {
        $this->assertInstanceOf(ServiceDelegateDefinition::class, $this->definition);
    }

    public function testGetDelegateTypeIsServiceFactory() {
        $this->assertSame(Fixtures::delegatedService()->serviceFactory(), $this->definition->delegateType());
    }

    public function testGetDelegateMethodIsCorrect() {
        $this->assertSame('createService', $this->definition->delegateMethod());
    }

    public function testGetServiceType() {
        $this->assertSame(Fixtures::delegatedService()->serviceInterface(), $this->definition->serviceType());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(ServiceDelegate::class, $this->definition->attribute());
    }
}

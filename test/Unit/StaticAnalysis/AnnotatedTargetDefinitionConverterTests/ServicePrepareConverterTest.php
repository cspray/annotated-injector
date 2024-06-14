<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use ReflectionMethod;

class ServicePrepareConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::ServicePrepare, new ReflectionMethod(
            Fixtures::interfacePrepareServices()->fooInterface()->getName(),
            'setBar'
        ));
    }

    public function testGetServiceDelegateDefinitionInstance() {
        $this->assertInstanceOf(ServicePrepareDefinition::class, $this->definition);
    }

    public function testGetService() {
        $this->assertSame(Fixtures::interfacePrepareServices()->fooInterface(), $this->definition->service());
    }

    public function testGetMethodIsCorrect() {
        $this->assertSame('setBar', $this->definition->methodName());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(ServicePrepare::class, $this->definition->attribute());
    }
}

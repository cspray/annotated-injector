<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use ReflectionClass;
use function Cspray\Typiphy\objectType;

class SingleConcreteServiceConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    private function getClass() : string {
        return Fixtures::singleConcreteService()->fooImplementation()->getName();
    }

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Service, reflection: new ReflectionClass($this->getClass()));
    }

    public function testGetServiceDefinitionInstance() {
        $this->assertInstanceOf(ServiceDefinition::class, $this->definition);
    }

    public function testGetServiceDefinitionType() {
        $this->assertSame(objectType($this->getClass()), $this->definition->type());
    }

    public function testServiceIsConcrete() {
        $this->assertTrue($this->definition->isConcrete());
    }

    public function testServiceNameIsNull() {
        $this->assertNull($this->definition->name());
    }

    public function testServiceIsPrimary() {
        $this->assertFalse($this->definition->isPrimary());
    }

    public function testServiceProfiles() {
        $this->assertSame(['default'], $this->definition->profiles());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(Service::class, $this->definition->attribute());
    }
}

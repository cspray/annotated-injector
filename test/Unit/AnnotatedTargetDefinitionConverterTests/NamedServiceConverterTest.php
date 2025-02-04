<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use ReflectionClass;

class NamedServiceConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Service, new ReflectionClass(Fixtures::namedServices()->fooInterface()->getName()));
    }
    public function testGetServiceDefinitionInstance() {
        $this->assertInstanceOf(ServiceDefinition::class, $this->definition);
    }

    public function testGetServiceDefinitionType() {
        $this->assertSame(Fixtures::namedServices()->fooInterface(), $this->definition->getType());
    }

    public function testServiceIsAbstract() {
        $this->assertTrue($this->definition->isAbstract());
    }

    public function testServiceName() {
        $this->assertSame('foo', $this->definition->getName());
    }

    public function testServiceIsPrimary() {
        $this->assertFalse($this->definition->isPrimary());
    }

    public function testServiceProfiles() {
        $this->assertSame(['default'], $this->definition->getProfiles());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(Service::class, $this->definition->getAttribute());
        self::assertSame('foo', $this->definition->getAttribute()->getName());
    }
}

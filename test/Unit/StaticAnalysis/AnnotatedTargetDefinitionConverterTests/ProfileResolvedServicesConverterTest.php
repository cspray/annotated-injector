<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use ReflectionClass;

class ProfileResolvedServicesConverterTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Service, new ReflectionClass(Fixtures::profileResolvedServices()->devImplementation()->getName()));
    }

    public function testGetServiceDefinitionInstance() {
        $this->assertInstanceOf(ServiceDefinition::class, $this->definition);
    }

    public function testGetServiceDefinitionType() {
        $this->assertSame(Fixtures::profileResolvedServices()->devImplementation(), $this->definition->type());
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
        $this->assertSame(['dev'], $this->definition->profiles());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(Service::class, $this->definition->attribute());
        self::assertSame(['dev'], $this->definition->attribute()->profiles());
    }
}

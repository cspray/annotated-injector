<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\ServiceDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use ReflectionClass;

class MultipleServicesWithPrimaryTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Service,
            new ReflectionClass(Fixtures::primaryAliasedServices()->fooImplementation()->getName())
        );
    }
    public function testGetServiceDefinitionInstance() {
        $this->assertInstanceOf(ServiceDefinition::class, $this->definition);
    }

    public function testGetServiceDefinitionType() {
        $this->assertSame(Fixtures::primaryAliasedServices()->fooImplementation(), $this->definition->getType());
    }

    public function testServiceIsConcrete() {
        $this->assertTrue($this->definition->isConcrete());
    }

    public function testServiceNameIsNull() {
        $this->assertNull($this->definition->getName());
    }

    public function testServiceIsPrimary() {
        $this->assertTrue($this->definition->isPrimary());
    }

    public function testServiceProfiles() {
        $this->assertSame(['default'], $this->definition->getProfiles());
    }

}
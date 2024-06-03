<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\typeUnion;

class InjectServiceTypeUnionPropertyTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionProperty(Fixtures::injectServiceIntersectConstructorServices()->fooBarConfiguration()->getName(), 'fooOrBar')
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectServiceIntersectConstructorServices()->fooBarConfiguration(), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetPropertyName() {
        $this->assertSame('fooOrBar', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetTypeIntersect() {
        $this->assertSame(
            typeUnion(Fixtures::injectServiceIntersectConstructorServices()->fooInterface(), Fixtures::injectServiceIntersectConstructorServices()->barInterface()),
            $this->definition->getType()
        );
    }

    public function testGetValue() {
        $this->assertSame(Fixtures::injectServiceIntersectConstructorServices()->barImplementation()->getName(), $this->definition->getValue());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->getStoreName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->getProfiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->getAttribute());
        self::assertSame(Fixtures::injectServiceIntersectConstructorServices()->barImplementation()->getName(), $this->definition->getAttribute()->getValue());
    }
}

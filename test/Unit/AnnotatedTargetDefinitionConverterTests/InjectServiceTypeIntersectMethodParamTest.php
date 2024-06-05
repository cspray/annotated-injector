<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\typeIntersect;

class InjectServiceTypeIntersectMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter([Fixtures::injectServiceIntersectConstructorServices()->fooBarConsumer()->getName(), '__construct'], 'fooBar')
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectServiceIntersectConstructorServices()->fooBarConsumer(), $this->definition->targetIdentifier()->class());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->targetIdentifier()->methodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('fooBar', $this->definition->targetIdentifier()->name());
    }

    public function testDefinitionGetTypeIntersect() {
        $this->assertSame(
            typeIntersect(Fixtures::injectServiceIntersectConstructorServices()->fooInterface(), Fixtures::injectServiceIntersectConstructorServices()->barInterface()),
            $this->definition->type()
        );
    }

    public function testGetValue() {
        $this->assertSame(Fixtures::injectServiceIntersectConstructorServices()->fooBarImplementation()->getName(), $this->definition->value());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->storeName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->profiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->attribute());
        self::assertSame(Fixtures::injectServiceIntersectConstructorServices()->fooBarImplementation()->getName(), $this->definition->attribute()->getValue());
    }
}

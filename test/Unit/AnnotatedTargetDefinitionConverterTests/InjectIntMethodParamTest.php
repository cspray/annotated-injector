<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\intType;

class InjectIntMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter(
                [Fixtures::injectConstructorServices()->injectIntService()->getName(), '__construct'],
                'meaningOfLife'
            )
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectConstructorServices()->injectIntService(), $this->definition->targetIdentifier()->class());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->targetIdentifier()->methodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('meaningOfLife', $this->definition->targetIdentifier()->name());
    }

    public function testDefinitionGetType() {
        $this->assertSame(intType(), $this->definition->type());
    }

    public function testGetValue() {
        $this->assertSame(42, $this->definition->value());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->storeName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->profiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->attribute());
        self::assertSame(42, $this->definition->attribute()->getValue());
    }
}

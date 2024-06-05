<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\typeUnion;

class InjectServiceScalarTypeUnionMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter([Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector()->getName(), 'setValue'], 'val')
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), $this->definition->targetIdentifier()->class());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('setValue', $this->definition->targetIdentifier()->methodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('val', $this->definition->targetIdentifier()->name());
    }

    public function testDefinitionGetTypeUnion() {
        $this->assertSame(typeUnion(floatType(), Fixtures::injectPrepareServices()->fooInterface()), $this->definition->type());
    }

    public function testGetValue() {
        $this->assertSame(3.14, $this->definition->value());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->storeName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->profiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->attribute());
        self::assertSame(3.14, $this->definition->attribute()->getValue());
    }
}

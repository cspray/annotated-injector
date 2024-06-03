<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeUnion;

class InjectScalarTypeUnionMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter([Fixtures::injectConstructorServices()->injectTypeUnionService()->getName(), '__construct'], 'value')
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectConstructorServices()->injectTypeUnionService(), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('value', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetTypeUnion() {
        $this->assertSame(typeUnion(stringType(), intType(), floatType()), $this->definition->getType());
    }

    public function testGetValue() {
        $this->assertSame(4.20, $this->definition->getValue());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->getStoreName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->getProfiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->getAttribute());
        self::assertSame(4.20, $this->definition->getAttribute()->getValue());
    }
}

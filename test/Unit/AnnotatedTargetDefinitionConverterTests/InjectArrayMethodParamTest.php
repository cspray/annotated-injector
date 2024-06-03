<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\arrayType;

class InjectArrayMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter(
                [Fixtures::injectConstructorServices()->injectArrayService()->getName(), '__construct'],
                'values'
            )
        );
    }

    public function testDefinitionInstanceOf() : void {
        self::assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() : void {
        self::assertSame(Fixtures::injectConstructorServices()->injectArrayService(), $this->definition->getTargetIdentifier()->getClass());
    }

    public function testDefinitionGetMethod() : void {
        self::assertSame('__construct', $this->definition->getTargetIdentifier()->getMethodName());
    }

    public function testDefinitionGetParamName() : void {
        self::assertSame('values', $this->definition->getTargetIdentifier()->getName());
    }

    public function testDefinitionGetType() : void {
        self::assertSame(arrayType(), $this->definition->getType());
    }

    public function testGetValue() : void {
        self::assertSame(['dependency', 'injection', 'rocks'], $this->definition->getValue());
    }

    public function testGetStore() : void {
        self::assertNull($this->definition->getStoreName());
    }

    public function testGetProfiles() : void {
        self::assertSame(['default'], $this->definition->getProfiles());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(Inject::class, $this->definition->getAttribute());
        self::assertSame(['dependency', 'injection', 'rocks'], $this->definition->getAttribute()->getValue());
    }
}

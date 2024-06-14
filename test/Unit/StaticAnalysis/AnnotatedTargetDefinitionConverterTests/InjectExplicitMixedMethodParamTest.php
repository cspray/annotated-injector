<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\mixedType;

class InjectExplicitMixedMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter(
                [Fixtures::injectConstructorServices()->injectExplicitMixedService()->getName(), '__construct'],
                'value'
            )
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectConstructorServices()->injectExplicitMixedService(), $this->definition->class());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->methodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('value', $this->definition->parameterName());
    }

    public function testDefinitionGetType() {
        $this->assertSame(mixedType(), $this->definition->type());
    }

    public function testGetValue() {
        $this->assertSame('whatever', $this->definition->value());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->storeName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->profiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->attribute());
        self::assertSame('whatever', $this->definition->attribute()->getValue());
    }
}

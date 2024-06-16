<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\boolType;

class InjectBoolMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionParameter(
                [Fixtures::injectConstructorServices()->injectBoolService()->getName(), '__construct'],
                'flag'
            )
        );
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectConstructorServices()->injectBoolService(), $this->definition->class());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->methodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('flag', $this->definition->parameterName());
    }

    public function testDefinitionGetType() {
        $this->assertSame(boolType(), $this->definition->type());
    }

    public function testGetValue() {
        $this->assertFalse($this->definition->value());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->storeName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->profiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->attribute());
        self::assertFalse($this->definition->attribute()->value());
    }
}

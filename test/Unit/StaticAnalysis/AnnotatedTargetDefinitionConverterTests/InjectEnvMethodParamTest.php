<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\stringType;

class InjectEnvMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Inject, new \ReflectionParameter(
            [Fixtures::injectConstructorServices()->injectEnvService()->name(), '__construct'],
            'user'
        ));
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectConstructorServices()->injectEnvService(), $this->definition->class());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->methodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('user', $this->definition->parameterName());
    }

    public function testDefinitionGetType() {
        $this->assertSame(stringType(), $this->definition->type());
    }

    public function testGetValue() {
        $this->assertSame('USER', $this->definition->value());
    }

    public function testGetStore() {
        $this->assertSame('env', $this->definition->storeName());
    }

    public function testGetProfiles() {
        $this->assertSame(['default'], $this->definition->profiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->attribute());
        self::assertSame('USER', $this->definition->attribute()->value());
        self::assertSame('env', $this->definition->attribute()->from());
    }
}

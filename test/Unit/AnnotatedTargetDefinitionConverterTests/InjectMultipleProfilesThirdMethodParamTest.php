<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\stringType;

class InjectMultipleProfilesThirdMethodParamTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget(): AnnotatedTarget {
        return $this->getAnnotatedTarget(AttributeType::Inject, new \ReflectionParameter(
            [Fixtures::injectConstructorServices()->injectProfilesStringService()->getName(), '__construct'],
            'val'
        ), 2);
    }

    public function testDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testDefinitionGetService() {
        $this->assertSame(Fixtures::injectConstructorServices()->injectProfilesStringService(), $this->definition->targetIdentifier()->class());
    }

    public function testDefinitionGetMethod() {
        $this->assertSame('__construct', $this->definition->targetIdentifier()->methodName());
    }

    public function testDefinitionGetParamName() {
        $this->assertSame('val', $this->definition->targetIdentifier()->name());
    }

    public function testDefinitionGetType() {
        $this->assertSame(stringType(), $this->definition->type());
    }

    public function testGetValue() {
        $this->assertSame('from-prod', $this->definition->value());
    }

    public function testGetStore() {
        $this->assertNull($this->definition->storeName());
    }

    public function testGetProfiles() {
        $this->assertSame(['prod'], $this->definition->profiles());
    }

    public function testGetAttribute() {
        self::assertInstanceOf(Inject::class, $this->definition->attribute());
        self::assertSame('from-prod', $this->definition->attribute()->getValue());
    }
}

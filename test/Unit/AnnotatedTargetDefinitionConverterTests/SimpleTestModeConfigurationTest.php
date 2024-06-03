<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\boolType;

class SimpleTestModeConfigurationTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionProperty(Fixtures::configurationServices()->myConfig()->getName(), 'testMode')
        );
    }

    public function testGetDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testGetTargetReflectionClass() {
        $this->assertSame(Fixtures::configurationServices()->myConfig()->getName(), $this->definition->getTargetIdentifier()->getClass()->getName());
    }

    public function testGetTargetReflectionName() {
        $this->assertSame('testMode', $this->definition->getTargetIdentifier()->getName());
    }

    public function testGetDefinitionType() {
        $this->assertSame(boolType(), $this->definition->getType());
    }

    public function testGetDefinitionValue() {
        $this->assertSame(true, $this->definition->getValue());
    }

    public function testGetDefinitionProfiles() {
        $this->assertSame(['dev', 'test'], $this->definition->getProfiles());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(Inject::class, $this->definition->getAttribute());
    }
}

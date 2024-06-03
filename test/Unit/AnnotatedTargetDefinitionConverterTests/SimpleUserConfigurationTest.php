<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetDefinitionConverterTests;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use function Cspray\Typiphy\stringType;

class SimpleUserConfigurationTest extends AnnotatedTargetDefinitionConverterTestCase {

    protected function getSubjectTarget() : AnnotatedTarget {
        return $this->getAnnotatedTarget(
            AttributeType::Inject,
            new \ReflectionProperty(Fixtures::configurationServices()->myConfig()->getName(), 'user'),
        );
    }

    public function testGetDefinitionInstanceOf() {
        $this->assertInstanceOf(InjectDefinition::class, $this->definition);
    }

    public function testGetTargetReflectionClass() {
        $this->assertSame(Fixtures::configurationServices()->myConfig()->getName(), $this->definition->getTargetIdentifier()->getClass()->getName());
    }

    public function testGetTargetReflectionName() {
        $this->assertSame('user', $this->definition->getTargetIdentifier()->getName());
    }

    public function testGetDefinitionType() {
        $this->assertSame(stringType(), $this->definition->getType());
    }

    public function testGetDefinitionValue() {
        $this->assertSame('USER', $this->definition->getValue());
    }

    public function testGetDefinitionProfiles() {
        $this->assertSame(['default'], $this->definition->getProfiles());
    }

    public function testGetDefinitionStore() {
        $this->assertSame('env', $this->definition->getStoreName());
    }

    public function testGetAttribute() : void {
        self::assertInstanceOf(Inject::class, $this->definition->getAttribute());
    }
}

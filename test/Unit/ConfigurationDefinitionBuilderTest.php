<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinitionBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;

class ConfigurationDefinitionBuilderTest extends TestCase {

    public function testWithNameDifferentObject() {
        $configDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig());

        $a = $configDefinition->withName('foo');
        $b = $a->withName('bar');

        $this->assertNotSame($a, $b);
    }

    public function testWithAttributeDifferentObject() : void {
        $configDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig());

        self::assertNotSame($configDefinition, $configDefinition->withAttribute(new Configuration()));
    }

    public function testForServiceBuild() {
        $configurationDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())->build();

        $this->assertSame(Fixtures::configurationServices()->myConfig(), $configurationDefinition->getClass());
    }

    public function testWithNameBuild() {
        $configurationDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())
            ->withName('my-config')
            ->build();

        $this->assertSame('my-config', $configurationDefinition->getName());
    }

    public function testGetAttributeIsNull() : void {
        $configDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())
            ->build();

        self::assertNull($configDefinition->getAttribute());
    }

    public function testGetAttributeIsSameInstance() : void {
        $configDefinition = ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())
            ->withAttribute($attr = new Configuration())
            ->build();

        self::assertSame($attr, $configDefinition->getAttribute());
    }
}

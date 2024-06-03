<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\objectType;

class ServiceDefinitionBuilderTest extends TestCase {

    public function factoryMethodProvider() {
        return [
            ['forAbstract'],
            ['forConcrete']
        ];
    }

    private function getAbstractType() : string {
        return Fixtures::implicitAliasedServices()->fooInterface()->getName();
    }

    private function getConcreteType() : string {
        return Fixtures::implicitAliasedServices()->fooImplementation()->getName();
    }

    public function testBuildingTypeForAbstractHasCorrectServiceDefinitionType() {
        $type = objectType($this->getAbstractType());
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract($type)->build();

        $this->assertSame($type, $serviceDefinition->getType());
    }

    public function testBuildingTypeForAbstractIsAbstract() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType($this->getAbstractType()))->build();

        $this->assertTrue($serviceDefinition->isAbstract());
    }

    public function testBuildingTypeForAbstractIsNotConcrete() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType($this->getAbstractType()))->build();

        $this->assertFalse($serviceDefinition->isConcrete());
    }

    public function testBuildingTypeForAbstractWithNoProfilesSpecifiedIncludesDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType($this->getAbstractType()))->build();

        $this->assertSame(['default'], $serviceDefinition->getProfiles());
    }

    public function testBuildingTypeForConcreteHasCorrectServiceDefinitionType() {
        $type = objectType($this->getConcreteType());
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete($type)->build();

        $this->assertSame($type, $serviceDefinition->getType());
    }

    public function testBuildingTypeForConcreteIsNotAbstract() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType($this->getConcreteType()))->build();

        $this->assertFalse($serviceDefinition->isAbstract());
    }

    public function testBuildingTypeForConcreteIsConcrete() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType($this->getConcreteType()))->build();

        $this->assertTrue($serviceDefinition->isConcrete());
    }

    public function testWithProfilesImmutableBuilder() {
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(objectType($this->getConcreteType()));
        $serviceDefinition2 = $serviceDefinition1->withProfiles(['dev']);

        $this->assertNotSame($serviceDefinition1, $serviceDefinition2);
    }

    public function testWithNameImmutableBuilder() : void {
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(objectType($this->getConcreteType()));
        $serviceDefinition2 = $serviceDefinition1->withName('name');

        self::assertNotSame($serviceDefinition1, $serviceDefinition2);
    }

    public function testWithAttributeImmutableBuilder() : void {
        $serviceDefinition1 = ServiceDefinitionBuilder::forConcrete(objectType($this->getConcreteType()));
        $serviceDefinition2 = $serviceDefinition1->withAttribute(new Service());

        self::assertNotSame($serviceDefinition1, $serviceDefinition2);
    }

    public function testWithProfileReplacesDefault() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType($this->getConcreteType()))
            ->withProfiles(['dev'])->build();

        $this->assertSame(['dev'], $serviceDefinition->getProfiles());
    }

    public function testWithMultipleProfilesAllIncluded() {
        $serviceDefinition = ServiceDefinitionBuilder::forConcrete(objectType($this->getConcreteType()))
            ->withProfiles(['default', 'dev', 'local'])
            ->build();

        $this->assertSame(['default', 'dev', 'local'], $serviceDefinition->getProfiles());
    }

    public function testWithNoAttributeDefinitionAttributeIsNull() : void {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType($this->getAbstractType()))->build();

        self::assertNull($serviceDefinition->getAttribute());
    }

    public function testWithAttributeDefinitionAttributeIsSame() : void {
        $serviceDefinition = ServiceDefinitionBuilder::forAbstract(objectType($this->getAbstractType()))
            ->withAttribute($attr = new Service())
            ->build();

        self::assertSame($attr, $serviceDefinition->getAttribute());
    }
}

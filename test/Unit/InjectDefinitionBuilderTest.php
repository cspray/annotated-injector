<?php

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidInjectDefinition;
use Cspray\AnnotatedContainerFixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\stringType;

class InjectDefinitionBuilderTest extends TestCase {

    public function testInjectDefinitionWithNoMethodOrPropertyThrowsException() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->expectException(InvalidInjectDefinition::class);
        $this->expectExceptionMessage('A method or property to inject into MUST be provided before building an InjectDefinition.');
        $builder->build();
    }

    public function testInjectDefinitionWithMethodAndPropertyThrowsException() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $builder = $builder->withMethod('does-not-matter', stringType(), 'else')->withProperty(stringType(), 'else');

        $this->expectException(InvalidInjectDefinition::class);
        $this->expectExceptionMessage('A method and property MUST NOT be set together when building an InjectDefinition.');
        $builder->build();
    }

    public function testInjectDefinitionWithoutValueThrowsException() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector());

        $builder = $builder->withMethod('does-not-matter', stringType(), 'else');

        $this->expectException(InvalidInjectDefinition::class);
        $this->expectExceptionMessage('A value MUST be provided when building an InjectDefinition.');
        $builder->build();
    }

    public function testInjectDefinitionWithMethodHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->assertNotSame($builder, $builder->withMethod('foo', stringType(), 'baz'));
    }

    public function testInjectDefinitionWithPropertyHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->assertNotSame($builder, $builder->withProperty(stringType(), 'bar'));
    }

    public function testInjectDefinitionWithValueHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->assertNotSame($builder, $builder->withValue('foo'));
    }

    public function testInjectDefinitionWithStoreHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->assertNotSame($builder, $builder->withStore('foo-store'));
    }

    public function testInjectDefinitionWithProfilesHasDifferentObject() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->assertNotSame($builder, $builder->withProfiles('profile'));
    }

    public function testInjectDefinitionWithAttributeHasDifferentObject() : void {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->assertNotSame($builder, $builder->withAttribute(new Inject('my-value')));
    }

    public function testValidMethodInjectDefinitionGetTargetIdentifierIsMethod() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertTrue($injectDefinition->targetIdentifier()->isMethodParameter());
    }

    public function testValidMethodInjectDefinitionGetTargetIdentifierIsProperty() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertFalse($injectDefinition->targetIdentifier()->isClassProperty());
    }

    public function testValidMethodInjectDefinitionGetTargetIdentifierGetName() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('paramName', $injectDefinition->targetIdentifier()->name());
    }

    public function testValidMethodInjectDefinitionTargetIdentifierGetValue() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('methodName', $injectDefinition->targetIdentifier()->methodName());
    }

    public function testValidMethodInjectDefinitionTargetIdentifierGetClass() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame(Fixtures::injectPrepareServices()->prepareInjector(), $injectDefinition->targetIdentifier()->class());
    }

    public function testValidMethodInjectDefinitionGetType() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', $expectedType = stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame($expectedType, $injectDefinition->type());
    }

    public function testValidMethodInjectDefinitionGetValue() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('foobar', $injectDefinition->value());
    }

    public function testValidMethodInjectDefinitionWithNoProfilesGetProfiles() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame(['default'], $injectDefinition->profiles());
    }

    public function testValidMethodInjectDefinitionWithOneProfileGetProfiles() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->withProfiles('foo')
            ->build();

        $this->assertSame(['foo'], $injectDefinition->profiles());
    }

    public function testValidMethodInjectDefinitionWithAdditionalProfilesGetProfiles() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->withProfiles('foo', 'bar', 'baz')
            ->build();

        $this->assertSame(['foo', 'bar', 'baz'], $injectDefinition->profiles());
    }

    public function testValidPropertyInjectDefinitionGetTargetIdentifierIsMethod() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withProperty(stringType(), 'key')
            ->withValue('my-api-key')
            ->build();

        $this->assertFalse($injectDefinition->targetIdentifier()->isMethodParameter());
    }

    public function testValidPropertyInjectDefinitionGetTargetIdentifierIsProperty() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withProperty(stringType(), 'key')
            ->withValue('my-api-key')
            ->build();

        $this->assertTrue($injectDefinition->targetIdentifier()->isClassProperty());
    }

    public function testValidPropertyInjectDefinitionGetTargetIdentifierGetName() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withProperty(stringType(), 'key')
            ->withValue('my-api-key')
            ->build();

        $this->assertSame('key', $injectDefinition->targetIdentifier()->name());
    }

    public function testValidPropertyInjectDefinitionGetTargetIdentifierGetClass() {
        $injectDefinition = InjectDefinitionBuilder::forService($classType = Fixtures::injectPrepareServices()->prepareInjector())
            ->withProperty(stringType(), 'key')
            ->withValue('my-api-key')
            ->build();

        $this->assertSame($classType, $injectDefinition->targetIdentifier()->class());
    }

    public function testValidPropertyInjectDefinitionGetTargetIdentifierGetMethod() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withProperty(stringType(), 'key')
            ->withValue('my-api-key')
            ->build();

        $this->assertNull($injectDefinition->targetIdentifier()->methodName());
    }

    public function testWithNoAttributeReturnsInjectDefinitionWithNullAttribute() : void {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withProperty(stringType(), 'key')
            ->withValue('my-api-key')
            ->build();

        self::assertNull($injectDefinition->attribute());
    }

    public function testWithAttributeReturnsSameInstance() : void {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withProperty(stringType(), 'key')
            ->withValue('my-api-key')
            ->withAttribute($attr = new Inject("my-inject-value"))
            ->build();

        self::assertSame($attr, $injectDefinition->attribute());
    }
}

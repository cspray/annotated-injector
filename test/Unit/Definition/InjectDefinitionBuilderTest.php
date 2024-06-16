<?php

namespace Cspray\AnnotatedContainer\Unit\Definition;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidInjectDefinition;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\stringType;

class InjectDefinitionBuilderTest extends TestCase {

    public function testInjectDefinitionWithNoMethodOrPropertyThrowsException() {
        $builder = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector());

        $this->expectException(InvalidInjectDefinition::class);
        $this->expectExceptionMessage('A method to inject into MUST be provided before building an InjectDefinition.');
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

    public function testValidMethodInjectDefinitionGetTargetIdentifierGetName() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('paramName', $injectDefinition->parameterName());
    }

    public function testValidMethodInjectDefinitionTargetIdentifierGetValue() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame('methodName', $injectDefinition->methodName());
    }

    public function testValidMethodInjectDefinitionTargetIdentifierGetClass() {
        $injectDefinition = InjectDefinitionBuilder::forService(Fixtures::injectPrepareServices()->prepareInjector())
            ->withMethod('methodName', stringType(), 'paramName')
            ->withValue('foobar')
            ->build();

        $this->assertSame(Fixtures::injectPrepareServices()->prepareInjector(), $injectDefinition->class());
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
}

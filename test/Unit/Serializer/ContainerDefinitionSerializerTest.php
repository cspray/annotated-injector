<?php

namespace Cspray\AnnotatedContainer\Unit\Serializer;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidInjectDefinition;
use Cspray\AnnotatedContainer\Serializer\ContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Unit\Helper\UnserializableObject;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\CardinalDirections;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class ContainerDefinitionSerializerTest extends TestCase {

    private const BASE_64_ENCODED_STRING = 'c3RyaW5n';
    private const BASE_64_ENCODED_INT = 'aW50';
    private const BASE_64_ENCODED_CARDINAL_DIRECTIONS = 'Q3NwcmF5XEFubm90YXRlZENvbnRhaW5lckZpeHR1cmVcSW5qZWN0RW51bUNvbnN0cnVjdG9yU2VydmljZXNcQ2FyZGluYWxEaXJlY3Rpb25z';

    public function testSerializingSingleConcreteService() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingSingleConcreteServiceWithAttribute() : void {
        $attributeVal = base64_encode(serialize($attr = new Service()));
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute>{$attributeVal}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->withAttribute($attr)
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceWithExplicitProfiles() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>my-profile</profile>
        <profile>my-other-profile</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->withProfiles(['my-profile', 'my-other-profile'])
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServicesWithAliases() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions>
    <aliasDefinition>
      <abstractService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</abstractService>
      <concreteService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</concreteService>
    </aliasDefinition>
  </aliasDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::implicitAliasedServices()->fooInterface())->build()
            )->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::implicitAliasedServices()->fooImplementation())->build()
            )->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract(Fixtures::implicitAliasedServices()->fooInterface())
                    ->withConcrete(Fixtures::implicitAliasedServices()->fooImplementation())
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceIsPrimary() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition isPrimary="true">
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation(), true)->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceWithName() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name>my-name</name>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->withName('my-name')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServicePrepareDefinition() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions>
    <servicePrepareDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <method>setBar</method>
      <attribute/>
    </servicePrepareDefinition>
  </servicePrepareDefinitions>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::interfacePrepareServices()->fooInterface())
                    ->build()
            )->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod(
                    Fixtures::interfacePrepareServices()->fooInterface(),
                    'setBar'
                )->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServicePrepareDefinitionWithAttribute() : void {
        $attrVal = base64_encode(serialize(new ServicePrepare()));
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions>
    <servicePrepareDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <method>setBar</method>
      <attribute>{$attrVal}</attribute>
    </servicePrepareDefinition>
  </servicePrepareDefinitions>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::interfacePrepareServices()->fooInterface())
                    ->build()
            )->withServicePrepareDefinition(
                ServicePrepareDefinitionBuilder::forMethod(
                    Fixtures::interfacePrepareServices()->fooInterface(),
                    'setBar'
                )->withAttribute(new ServicePrepare())->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceDelegateDefinition() : void {
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions>
    <serviceDelegateDefinition>
      <service>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</service>
      <delegateType>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceFactory</delegateType>
      <delegateMethod>createService</delegateMethod>
      <attribute/>
    </serviceDelegateDefinition>
  </serviceDelegateDefinitions>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::delegatedService()->serviceInterface())->build()
            )->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
                    ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServiceDelegateDefinitionWithAttribute() : void {
        $attrVal = base64_encode(serialize(new ServiceDelegate()));
        $version = AnnotatedContainerVersion::version();
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions>
    <serviceDelegateDefinition>
      <service>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</service>
      <delegateType>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceFactory</delegateType>
      <delegateMethod>createService</delegateMethod>
      <attribute>{$attrVal}</attribute>
    </serviceDelegateDefinition>
  </serviceDelegateDefinitions>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forAbstract(Fixtures::delegatedService()->serviceInterface())->build()
            )->withServiceDelegateDefinition(
                ServiceDelegateDefinitionBuilder::forService(Fixtures::delegatedService()->serviceInterface())
                    ->withDelegateMethod(Fixtures::delegatedService()->serviceFactory(), 'createService')
                    ->withAttribute(new ServiceDelegate())
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterStringValue() : void {
        $attrVal = base64_encode(serialize(new Inject('foobar')));
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize('foobar'));
        $type = self::BASE_64_ENCODED_STRING;
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
      <attribute>{$attrVal}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectConstructorServices()->injectStringService())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectStringService())
                ->withMethod('__construct', stringType(), 'val')
                ->withValue('foobar')
                ->withAttribute(new Inject('foobar'))
                ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterIntValue() : void {
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize(42));
        $type = self::BASE_64_ENCODED_INT;
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\IntInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\IntInjectService</class>
          <method>__construct</method>
          <parameter>meaningOfLife</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectConstructorServices()->injectIntService())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectIntService())
                    ->withMethod('__construct', intType(), 'meaningOfLife')
                    ->withValue(42)
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterUnitEnumValue() : void {
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize(CardinalDirections::West));
        $type = self::BASE_64_ENCODED_CARDINAL_DIRECTIONS;
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</class>
          <method>__construct</method>
          <parameter>directions</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectEnumConstructorServices()->enumInjector())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectEnumConstructorServices()->enumInjector())
                    ->withMethod('__construct', objectType(CardinalDirections::class), 'directions')
                    ->withValue(CardinalDirections::West)
                    ->build()
            )->build();

        $subject = new ContainerDefinitionSerializer();
        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterWithStore() : void {
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize('key'));
        $type = self::BASE_64_ENCODED_STRING;
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</class>
          <method>__construct</method>
          <parameter>key</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store>test-store</store>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;


        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectCustomStoreServices()->scalarInjector())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectCustomStoreServices()->scalarInjector())
                    ->withMethod('__construct', stringType(), 'key')
                    ->withStore('test-store')
                    ->withValue('key')
                    ->build()
            )->build();

        $subject = new ContainerDefinitionSerializer();
        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectMethodParameterExplicitProfiles() : void {
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize('foobar'));
        $type = self::BASE_64_ENCODED_STRING;
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>foo</profile>
        <profile>baz</profile>
      </profiles>
      <store/>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectConstructorServices()->injectStringService())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectConstructorServices()->injectStringService())
                    ->withMethod('__construct', stringType(), 'val')
                    ->withValue('foobar')
                    ->withProfiles('foo', 'baz')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingInjectDefinitionWithUnserializableValueThrowsException() : void {
        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::injectEnumConstructorServices()->enumInjector())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::injectEnumConstructorServices()->enumInjector())
                    ->withMethod('__construct', objectType(UnserializableObject::class), 'directions')
                    ->withValue(new UnserializableObject())
                    ->build()
            )->build();

        $subject = new ContainerDefinitionSerializer();

        $this->expectException(InvalidInjectDefinition::class);
        $this->expectExceptionMessage('An InjectDefinition with a value that cannot be serialized was provided.');

        $subject->serialize($containerDefinition);
    }

    public function testDeserializingConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->type());
        self::assertSame(['default'], $serviceDefinition->profiles());
        self::assertNull($serviceDefinition->name());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingNamedConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name>my_service_name</name>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->type());
        self::assertSame(['default'], $serviceDefinition->profiles());
        self::assertSame('my_service_name', $serviceDefinition->name());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingPrimaryConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition isPrimary="true">
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->type());
        self::assertSame(['default'], $serviceDefinition->profiles());
        self::assertNull($serviceDefinition->name());
        self::assertTrue($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingServiceDefinitionWithProfiles() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition isPrimary="false">
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>foo</profile>
        <profile>bar</profile>
        <profile>baz</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->type());
        self::assertSame(['foo', 'bar', 'baz'], $serviceDefinition->profiles());
        self::assertNull($serviceDefinition->name());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializeAbstractServiceDefinition() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;
        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::implicitAliasedServices()->fooInterface(), $serviceDefinition->type());
        self::assertSame(['default'], $serviceDefinition->profiles());
        self::assertNull($serviceDefinition->name());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertFalse($serviceDefinition->isConcrete());
        self::assertTrue($serviceDefinition->isAbstract());
    }

    public function testDeserializeAliasDefinitions() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions>
    <aliasDefinition>
      <abstractService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooInterface</abstractService>
      <concreteService>Cspray\AnnotatedContainerFixture\ImplicitAliasedServices\FooImplementation</concreteService>
    </aliasDefinition>
  </aliasDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->aliasDefinitions());
        $aliasDefinition = $actual->aliasDefinitions()[0];
        self::assertSame(Fixtures::implicitAliasedServices()->fooInterface(), $aliasDefinition->abstractService());
        self::assertSame(Fixtures::implicitAliasedServices()->fooImplementation(), $aliasDefinition->concreteService());
    }

    public function testDeserializeServicePrepareDefinitions() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions>
    <servicePrepareDefinition>
      <type>Cspray\AnnotatedContainerFixture\InterfacePrepareServices\FooInterface</type>
      <method>setBar</method>
      <attribute/>
    </servicePrepareDefinition>
  </servicePrepareDefinitions>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->servicePrepareDefinitions());
        $prepareDefinition = $actual->servicePrepareDefinitions()[0];
        self::assertSame(Fixtures::interfacePrepareServices()->fooInterface(), $prepareDefinition->service());
        self::assertSame('setBar', $prepareDefinition->methodName());
    }

    public function testDeserializeServiceDelegateDefinitions() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Abstract</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions>
    <serviceDelegateDefinition>
      <service>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceInterface</service>
      <delegateType>Cspray\AnnotatedContainerFixture\DelegatedService\ServiceFactory</delegateType>
      <delegateMethod>createService</delegateMethod>
      <attribute/>
    </serviceDelegateDefinition>
  </serviceDelegateDefinitions>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->serviceDelegateDefinitions());
        $delegateDefinition = $actual->serviceDelegateDefinitions()[0];

        self::assertSame(Fixtures::delegatedService()->serviceInterface(), $delegateDefinition->serviceType());
        self::assertSame(Fixtures::delegatedService()->serviceFactory(), $delegateDefinition->delegateType());
        self::assertSame('createService', $delegateDefinition->delegateMethod());
    }

    public function testDeserializeInjectMethodParameter() : void {
        $type = self::BASE_64_ENCODED_STRING;
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize('foobar'));
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->targetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->targetIdentifier()->class()
        );
        self::assertSame('__construct', $injectDefinition->targetIdentifier()->methodName());
        self::assertSame('val', $injectDefinition->targetIdentifier()->name());
        self::assertSame(stringType(), $injectDefinition->type());
        self::assertSame('foobar', $injectDefinition->value());
        self::assertSame(['default'], $injectDefinition->profiles());
        self::assertNull($injectDefinition->storeName());
    }

    public function testDeserializeInjectDefinitionUnitEnumValueMethodParameter() : void {
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize(CardinalDirections::West));
        $type = self::BASE_64_ENCODED_CARDINAL_DIRECTIONS;
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectEnumConstructorServices\EnumInjector</class>
          <method>__construct</method>
          <parameter>directions</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store/>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->targetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectEnumConstructorServices()->enumInjector(),
            $injectDefinition->targetIdentifier()->class()
        );
        self::assertSame('__construct', $injectDefinition->targetIdentifier()->methodName());
        self::assertSame('directions', $injectDefinition->targetIdentifier()->name());
        self::assertSame(objectType(CardinalDirections::class), $injectDefinition->type());
        self::assertSame(CardinalDirections::West, $injectDefinition->value());
        self::assertSame(['default'], $injectDefinition->profiles());
        self::assertNull($injectDefinition->storeName());
    }

    public function testDeserializeInjectDefinitionMethodParameterWithStore() : void {
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize('key'));
        $type = self::BASE_64_ENCODED_STRING;
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectCustomStoreServices\ScalarInjector</class>
          <method>__construct</method>
          <parameter>key</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>default</profile>
      </profiles>
      <store>test-store</store>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->targetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectCustomStoreServices()->scalarInjector(),
            $injectDefinition->targetIdentifier()->class()
        );
        self::assertSame('__construct', $injectDefinition->targetIdentifier()->methodName());
        self::assertSame('key', $injectDefinition->targetIdentifier()->name());
        self::assertSame(stringType(), $injectDefinition->type());
        self::assertSame('key', $injectDefinition->value());
        self::assertSame(['default'], $injectDefinition->profiles());
        self::assertSame('test-store', $injectDefinition->storeName());
    }

    public function testDeserializeInjectMethodWithProfiles() : void {
        $version = AnnotatedContainerVersion::version();
        $encodedVal = base64_encode(serialize('annotated container'));
        $type = self::BASE_64_ENCODED_STRING;
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classMethod>
          <class>Cspray\AnnotatedContainerFixture\InjectConstructorServices\StringInjectService</class>
          <method>__construct</method>
          <parameter>val</parameter>
        </classMethod>
      </target>
      <valueType>{$type}</valueType>
      <value><![CDATA[{$encodedVal}]]></value>
      <profiles>
        <profile>foo</profile>
        <profile>baz</profile>
      </profiles>
      <store/>
      <attribute/>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->targetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->targetIdentifier()->class()
        );
        self::assertSame('__construct', $injectDefinition->targetIdentifier()->methodName());
        self::assertSame('val', $injectDefinition->targetIdentifier()->name());
        self::assertSame(stringType(), $injectDefinition->type());
        self::assertSame('annotated container', $injectDefinition->value());
        self::assertSame(['foo', 'baz'], $injectDefinition->profiles());
        self::assertNull($injectDefinition->storeName());
    }

    public function testDeserializeWithMismatchedVersionReturnsNull() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="not-up-to-date">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\SingleConcreteService\FooImplementation</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        self::assertNull($actual);
    }

    public static function fixturesDirProvider() : array {
        return [
            'singleConcreteService' => [Fixtures::singleConcreteService()],
            'injectConstructorServices' => [Fixtures::injectConstructorServices()],
            'interfacePrepareServices' => [Fixtures::interfacePrepareServices()],
            'injectPrepareServices' => [Fixtures::injectPrepareServices()],
            'delegatedService' => [Fixtures::delegatedService()],
            'implicitServiceDelegate' => [Fixtures::implicitServiceDelegateType()],
            'injectConstructorIntersectServices' => [Fixtures::injectServiceIntersectConstructorServices()]
        ];
    }

    #[DataProvider('fixturesDirProvider')]
    public function testScannedAndSerializedContainerDefinitionMatchesDeserialized(Fixture $fixture) : void {
        $compiler = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter(),
            new Emitter()
        );

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = $compiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($fixture->getPath())->build()
        );

        $expected = $subject->serialize($containerDefinition);
        $actual = $subject->serialize($subject->deserialize($expected));

        self::assertSame($expected, $actual);
    }
}

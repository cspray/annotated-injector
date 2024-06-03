<?php

namespace Cspray\AnnotatedContainer\Unit\Serializer;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Attribute\Configuration;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetDefinitionConverter;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinitionBuilder;
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
use PHPUnit\Framework\TestCase;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

class ContainerDefinitionSerializerTest extends TestCase {

    private const Base64EncodedString = 'c3RyaW5n';
    private const Base64EncodedInt = 'aW50';
    private const Base64EncodedCardinalDirections = 'Q3NwcmF5XEFubm90YXRlZENvbnRhaW5lckZpeHR1cmVcSW5qZWN0RW51bUNvbnN0cnVjdG9yU2VydmljZXNcQ2FyZGluYWxEaXJlY3Rpb25z';

    public function testSerializingSingleConcreteService() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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

    public function testSerializingConfigurationDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions>
    <configurationDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name/>
      <attribute/>
    </configurationDefinition>
  </configurationDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->build()
            )->withConfigurationDefinition(
                ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingConfigurationDefinitionWithAttribute() : void {
        $attrVal = base64_encode(serialize(new Configuration()));
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions>
    <configurationDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name/>
      <attribute>{$attrVal}</attribute>
    </configurationDefinition>
  </configurationDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->build()
            )->withConfigurationDefinition(
                ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())
                    ->withAttribute(new Configuration())
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingNamedConfigurationService() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions>
    <configurationDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name>config-name</name>
      <attribute/>
    </configurationDefinition>
  </configurationDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $containerDefinition = ContainerDefinitionBuilder::newDefinition()
            ->withServiceDefinition(
                ServiceDefinitionBuilder::forConcrete(Fixtures::singleConcreteService()->fooImplementation())
                    ->build()
            )->withConfigurationDefinition(
                ConfigurationDefinitionBuilder::forClass(Fixtures::configurationServices()->myConfig())
                    ->withName('config-name')
                    ->build()
            )->build();

        $actual = $subject->serialize($containerDefinition);

        self::assertSame($expected, $actual);
    }

    public function testSerializingServicePrepareDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('foobar'));
        $type = self::Base64EncodedString;
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize(42));
        $type = self::Base64EncodedInt;
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize(CardinalDirections::West));
        $type = self::Base64EncodedCardinalDirections;
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('key'));
        $type = self::Base64EncodedString;
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
  <configurationDefinitions/>
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
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('foobar'));
        $type = self::Base64EncodedString;
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
  <configurationDefinitions/>
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

    public function testSerializingInjectClassPropertyStringValue() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('my-api-key'));
        $type = self::Base64EncodedString;
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classProperty>
          <class>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</class>
          <property>key</property>
        </classProperty>
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
                ServiceDefinitionBuilder::forConcrete(Fixtures::configurationServices()->myConfig())->build()
            )->withInjectDefinition(
                InjectDefinitionBuilder::forService(Fixtures::configurationServices()->myConfig())
                    ->withProperty(stringType(), 'key')
                    ->withValue('my-api-key')
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
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingNamedConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertSame('my_service_name', $serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingPrimaryConcreteServiceDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertTrue($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingServiceDefinitionWithProfiles() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->getType());
        self::assertSame(['foo', 'bar', 'baz'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializeAbstractServiceDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;
        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        $serviceDefinitions = $actual->getServiceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::implicitAliasedServices()->fooInterface(), $serviceDefinition->getType());
        self::assertSame(['default'], $serviceDefinition->getProfiles());
        self::assertNull($serviceDefinition->getName());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertFalse($serviceDefinition->isConcrete());
        self::assertTrue($serviceDefinition->isAbstract());
    }

    public function testDeserializeAliasDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getAliasDefinitions());
        $aliasDefinition = $actual->getAliasDefinitions()[0];
        self::assertSame(Fixtures::implicitAliasedServices()->fooInterface(), $aliasDefinition->getAbstractService());
        self::assertSame(Fixtures::implicitAliasedServices()->fooImplementation(), $aliasDefinition->getConcreteService());
    }

    public function testDeserializeServicePrepareDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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

        self::assertCount(1, $actual->getServicePrepareDefinitions());
        $prepareDefinition = $actual->getServicePrepareDefinitions()[0];
        self::assertSame(Fixtures::interfacePrepareServices()->fooInterface(), $prepareDefinition->getService());
        self::assertSame('setBar', $prepareDefinition->getMethod());
    }

    public function testDeserializeServiceDelegateDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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

        self::assertCount(1, $actual->getServiceDelegateDefinitions());
        $delegateDefinition = $actual->getServiceDelegateDefinitions()[0];

        self::assertSame(Fixtures::delegatedService()->serviceInterface(), $delegateDefinition->getServiceType());
        self::assertSame(Fixtures::delegatedService()->serviceFactory(), $delegateDefinition->getDelegateType());
        self::assertSame('createService', $delegateDefinition->getDelegateMethod());
    }

    public function testDeserializeInjectMethodParameter() : void {
        $type = self::Base64EncodedString;
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions/>
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

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('val', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('foobar', $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeInjectDefinitionUnitEnumValueMethodParameter() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize(CardinalDirections::West));
        $type = self::Base64EncodedCardinalDirections;
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
  <configurationDefinitions/>
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

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectEnumConstructorServices()->enumInjector(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('directions', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(objectType(CardinalDirections::class), $injectDefinition->getType());
        self::assertSame(CardinalDirections::West, $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeInjectDefinitionMethodParameterWithStore() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('key'));
        $type = self::Base64EncodedString;
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
  <configurationDefinitions/>
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

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectCustomStoreServices()->scalarInjector(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('key', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('key', $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertSame('test-store', $injectDefinition->getStoreName());
    }

    public function testDeserializeInjectMethodWithProfiles() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('annotated container'));
        $type = self::Base64EncodedString;
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
  <configurationDefinitions/>
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

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isMethodParameter()
        );
        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('__construct', $injectDefinition->getTargetIdentifier()->getMethodName());
        self::assertSame('val', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('annotated container', $injectDefinition->getValue());
        self::assertSame(['foo', 'baz'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeInjectClassPropertyDefinition() : void {
        $version = AnnotatedContainerVersion::getVersion();
        $encodedVal = base64_encode(serialize('annotated container'));
        $type = self::Base64EncodedString;
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name/>
      <profiles>
        <profile>default</profile>
      </profiles>
      <concreteOrAbstract>Concrete</concreteOrAbstract>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <target>
        <classProperty>
          <class>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</class>
          <property>key</property>
        </classProperty>
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

        self::assertCount(1, $actual->getInjectDefinitions());
        $injectDefinition = $actual->getInjectDefinitions()[0];

        self::assertTrue(
            $injectDefinition->getTargetIdentifier()->isClassProperty()
        );
        self::assertSame(
            Fixtures::configurationServices()->myConfig(),
            $injectDefinition->getTargetIdentifier()->getClass()
        );
        self::assertSame('key', $injectDefinition->getTargetIdentifier()->getName());
        self::assertSame(stringType(), $injectDefinition->getType());
        self::assertSame('annotated container', $injectDefinition->getValue());
        self::assertSame(['default'], $injectDefinition->getProfiles());
        self::assertNull($injectDefinition->getStoreName());
    }

    public function testDeserializeConfigurationDefinitions() : void {
        $version = AnnotatedContainerVersion::getVersion();
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
  <configurationDefinitions>
    <configurationDefinition>
      <type>Cspray\AnnotatedContainerFixture\ConfigurationServices\MyConfig</type>
      <name>my-config</name>
      <attribute/>
    </configurationDefinition>
  </configurationDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();
        $actual = $subject->deserialize($xml);

        self::assertCount(1, $actual->getConfigurationDefinitions());
        $configurationDefinition = $actual->getConfigurationDefinitions()[0];

        self::assertSame(Fixtures::configurationServices()->myConfig(), $configurationDefinition->getClass());
        self::assertSame('my-config', $configurationDefinition->getName());
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
  <configurationDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new ContainerDefinitionSerializer();

        $actual = $subject->deserialize($xml);

        self::assertNull($actual);
    }

    public function fixturesDirProvider() : array {
        return [
            'singleConcreteService' => [Fixtures::singleConcreteService()],
            'injectConstructorServices' => [Fixtures::injectConstructorServices()],
            'interfacePrepareServices' => [Fixtures::interfacePrepareServices()],
            'injectPrepareServices' => [Fixtures::injectPrepareServices()],
            'delegatedService' => [Fixtures::delegatedService()],
            'implicitServiceDelegate' => [Fixtures::implicitServiceDelegateType()],
            'configurationServices' => [Fixtures::configurationServices()],
            'namedConfigurationServices' => [Fixtures::namedConfigurationServices()],
            'injectConstructorIntersectServices' => [Fixtures::injectServiceIntersectConstructorServices()]
        ];
    }

    /**
     * @dataProvider fixturesDirProvider
     */
    public function testScannedAndSerializedContainerDefinitionMatchesDeserialized(Fixture $fixture) : void {
        $compiler = new AnnotatedTargetContainerDefinitionAnalyzer(
            new PhpParserAnnotatedTargetParser(),
            new AnnotatedTargetDefinitionConverter()
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

<?php

namespace Cspray\AnnotatedContainer\Unit\Serializer;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\SerializedContainerDefinition;
use Cspray\AnnotatedContainer\Definition\Serializer\XmlContainerDefinitionSerializer;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Exception\InvalidSerializedContainerDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidInjectDefinition;
use Cspray\AnnotatedContainer\Exception\MismatchedContainerDefinitionSerializerVersions;
use Cspray\AnnotatedContainer\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptionsBuilder;
use Cspray\AnnotatedContainer\Unit\Helper\HasMockDefinitions;
use Cspray\AnnotatedContainer\Unit\Helper\UnserializableObject;
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Fixture\InjectEnumConstructorServices\CardinalDirections;
use Cspray\AnnotatedTarget\PhpParserAnnotatedTargetParser;
use Cspray\AssertThrows\ThrowableAssert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Cspray\AnnotatedContainer\Reflection\types;

final class XmlContainerDefinitionSerializerTest extends TestCase {

    use HasMockDefinitions;

    private function version() : string {
        return AnnotatedContainerVersion::version();
    }

    private function encodedAndSerialized(mixed $value) : string {
        return $this->encoded(serialize($value));
    }

    private function encoded(mixed $value) : string {
        return base64_encode($value);
    }

    private function assertSerializedContainerDefinitionEquals(string $expected, ContainerDefinition $containerDefinition) : void {
        $actual = (new XmlContainerDefinitionSerializer())->serialize($containerDefinition);

        self::assertSame($expected, $actual->asString());
    }

    public function testSerializingSingleConcreteService() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [$serviceDefinition = $this->concreteServiceDefinition(Fixtures::singleConcreteService()->fooImplementation())],
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>{$serviceDefinition->type()->name()}</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingServiceWithExplicitProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            [$serviceDefinition = $this->concreteServiceDefinition(
                Fixtures::singleConcreteService()->fooImplementation(),
                profiles: ['my-profile', 'my-other-profile']
            )]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingServicesWithAliases() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $abstractDefinition = $this->abstractServiceDefinition(Fixtures::implicitAliasedServices()->fooInterface()),
                $concreteDefinition = $this->concreteServiceDefinition(Fixtures::implicitAliasedServices()->fooImplementation()),
            ],
            aliasDefinitions: [
                $this->aliasDefinition(Fixtures::implicitAliasedServices()->fooInterface(), Fixtures::implicitAliasedServices()->fooImplementation())
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="false">
      <type>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooInterface</type>
      <attribute>{$this->encodedAndSerialized($abstractDefinition->attribute())}</attribute>
    </serviceDefinition>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized($concreteDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions>
    <aliasDefinition>
      <abstractService>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooInterface</abstractService>
      <concreteService>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooImplementation</concreteService>
    </aliasDefinition>
  </aliasDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingServiceIsPrimary() : void {
        $containerDefinition = $this->containerDefinition(
            [$serviceDefinition = $this->concreteServiceDefinition(
                Fixtures::singleConcreteService()->fooImplementation(),
                isPrimary: true
            )]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingServiceWithName() : void {
        $containerDefinition = $this->containerDefinition(
            [$serviceDefinition = $this->concreteServiceDefinition(Fixtures::singleConcreteService()->fooImplementation(), name: 'my-name')]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingServicePrepareDefinition() : void {
        $containerDefinition = $this->containerDefinition(
            [$serviceDefinition = $this->abstractServiceDefinition(Fixtures::interfacePrepareServices()->fooInterface())],
            servicePrepareDefinitions: [
                $servicePrepareDefinition = $this->servicePrepareDefinition(Fixtures::interfacePrepareServices()->fooInterface(), 'setBar')
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="false">
      <type>Cspray\AnnotatedContainer\Fixture\InterfacePrepareServices\FooInterface</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions>
    <servicePrepareDefinition>
      <type>Cspray\AnnotatedContainer\Fixture\InterfacePrepareServices\FooInterface</type>
      <method>setBar</method>
      <attribute>{$this->encodedAndSerialized($servicePrepareDefinition->attribute())}</attribute>
    </servicePrepareDefinition>
  </servicePrepareDefinitions>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingServiceDelegateDefinition() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->abstractServiceDefinition(Fixtures::delegatedService()->serviceInterface())
            ],
            serviceDelegateDefinitions: [
                $delegateDefinition = $this->serviceDelegateDefinition(
                    Fixtures::delegatedService()->serviceInterface(),
                    Fixtures::delegatedService()->serviceFactory(),
                    'createService'
                )
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="false">
      <type>Cspray\AnnotatedContainer\Fixture\DelegatedService\ServiceInterface</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions>
    <serviceDelegateDefinition>
      <service>Cspray\AnnotatedContainer\Fixture\DelegatedService\ServiceInterface</service>
      <delegateType>Cspray\AnnotatedContainer\Fixture\DelegatedService\ServiceFactory</delegateType>
      <delegateMethod>createService</delegateMethod>
      <attribute>{$this->encodedAndSerialized($delegateDefinition->attribute())}</attribute>
    </serviceDelegateDefinition>
  </serviceDelegateDefinitions>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingInjectMethodParameterStringValue() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->concreteServiceDefinition(Fixtures::injectConstructorServices()->injectStringService())
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    Fixtures::injectConstructorServices()->injectStringService(),
                    '__construct',
                    'val',
                    types()->string(),
                    'my string value',
                )
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</class>
      <method>__construct</method>
      <parameter>val</parameter>
      <valueType>
        <type>string</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingInjectMethodParameterIntValue() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $injectIntService = $this->concreteServiceDefinition(Fixtures::injectConstructorServices()->injectIntService()),
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    $injectIntService->type(),
                    '__construct',
                    'meaningOfLife',
                    types()->int(),
                    42
                )
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\IntInjectService</type>
      <attribute>{$this->encodedAndSerialized($injectIntService->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\IntInjectService</class>
      <method>__construct</method>
      <parameter>meaningOfLife</parameter>
      <valueType>
        <type>int</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingInjectMethodParameterUnitEnumValue() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->concreteServiceDefinition(Fixtures::injectEnumConstructorServices()->enumInjector())
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    $serviceDefinition->type(),
                    '__construct',
                    'directions',
                    types()->class(CardinalDirections::class),
                    CardinalDirections::West
                )
            ],
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectEnumConstructorServices\EnumInjector</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectEnumConstructorServices\EnumInjector</class>
      <method>__construct</method>
      <parameter>directions</parameter>
      <valueType>
        <type>Cspray\AnnotatedContainer\Fixture\InjectEnumConstructorServices\CardinalDirections</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingInjectMethodParameterWithStore() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->concreteServiceDefinition(Fixtures::injectCustomStoreServices()->scalarInjector())
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    $serviceDefinition->type(),
                    '__construct',
                    'key',
                    types()->string(),
                    'key',
                    store: 'test-store',
                ),
            ],
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectCustomStoreServices\ScalarInjector</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectCustomStoreServices\ScalarInjector</class>
      <method>__construct</method>
      <parameter>key</parameter>
      <valueType>
        <type>string</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingInjectMethodParameterExplicitProfiles() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->concreteServiceDefinition(Fixtures::injectConstructorServices()->injectStringService())
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    $serviceDefinition->type(),
                    '__construct',
                    'val',
                    types()->string(),
                    'foobar',
                    ['foo', 'baz']
                ),
            ],
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</class>
      <method>__construct</method>
      <parameter>val</parameter>
      <valueType>
        <type>string</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializeInjectWithTypeUnion() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->concreteServiceDefinition(Fixtures::injectUnionCustomStoreServices()->unionInjector())
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    $serviceDefinition->type(),
                    '__construct',
                    'fooOrBar',
                    types()->union(
                        Fixtures::injectUnionCustomStoreServices()->fooInterface(),
                        Fixtures::injectUnionCustomStoreServices()->barInterface()
                    ),
                    'foo',
                    store: 'union-store'
                )
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\UnionInjector</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\UnionInjector</class>
      <method>__construct</method>
      <parameter>fooOrBar</parameter>
      <valueType>
        <typeUnion>
          <type>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\FooInterface</type>
          <type>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\BarInterface</type>
        </typeUnion>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializeInjectWithTypeIntersect() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->concreteServiceDefinition(Fixtures::injectIntersectCustomStoreServices()->intersectInjector())
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    $serviceDefinition->type(),
                    '__construct',
                    'fooAndBar',
                    types()->intersect(
                        Fixtures::injectIntersectCustomStoreServices()->fooInterface(),
                        Fixtures::injectIntersectCustomStoreServices()->barInterface()
                    ),
                    'foobar',
                    store: 'intersect-store'
                )
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectIntersectCustomStoreServices\IntersectInjector</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectIntersectCustomStoreServices\IntersectInjector</class>
      <method>__construct</method>
      <parameter>fooAndBar</parameter>
      <valueType>
        <typeIntersect>
          <type>Cspray\AnnotatedContainer\Fixture\InjectIntersectCustomStoreServices\FooInterface</type>
          <type>Cspray\AnnotatedContainer\Fixture\InjectIntersectCustomStoreServices\BarInterface</type>
        </typeIntersect>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializeInjectWithTypeUnionAndTypeIntersect() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $serviceDefinition = $this->concreteServiceDefinition(Fixtures::injectUnionCustomStoreServices()->unionInjector())
            ],
            injectDefinitions: [
                $injectDefinition = $this->injectDefinition(
                    $serviceDefinition->type(),
                    '__construct',
                    'fooOrBar',
                    types()->union(
                        Fixtures::injectUnionCustomStoreServices()->fooInterface(),
                        Fixtures::injectUnionCustomStoreServices()->barInterface(),
                        types()->intersect(
                            Fixtures::injectIntersectCustomStoreServices()->barInterface(),
                            Fixtures::injectIntersectCustomStoreServices()->fooInterface(),
                        ),
                    ),
                    'foo',
                    store: 'union-store'
                )
            ]
        );
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\UnionInjector</type>
      <attribute>{$this->encodedAndSerialized($serviceDefinition->attribute())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\UnionInjector</class>
      <method>__construct</method>
      <parameter>fooOrBar</parameter>
      <valueType>
        <typeUnion>
          <type>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\FooInterface</type>
          <type>Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices\BarInterface</type>
          <typeIntersect>
            <type>Cspray\AnnotatedContainer\Fixture\InjectIntersectCustomStoreServices\BarInterface</type>
            <type>Cspray\AnnotatedContainer\Fixture\InjectIntersectCustomStoreServices\FooInterface</type>
          </typeIntersect>
        </typeUnion>
      </valueType>
      <attribute>{$this->encodedAndSerialized($injectDefinition->attribute())}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $this->assertSerializedContainerDefinitionEquals($expected, $containerDefinition);
    }

    public function testSerializingInjectDefinitionWithUnserializableValueThrowsException() : void {
        $containerDefinition = $this->containerDefinition(
            serviceDefinitions: [
                $this->concreteServiceDefinition(Fixtures::injectEnumConstructorServices()->enumInjector())
            ],
            injectDefinitions: [
                $this->injectDefinition(
                    Fixtures::injectEnumConstructorServices()->enumInjector(),
                    '__construct',
                    'directions',
                    types()->class(UnserializableObject::class),
                    new UnserializableObject()
                ),
            ],
        );

        $subject = new XmlContainerDefinitionSerializer();

        $this->expectException(InvalidInjectDefinition::class);
        $this->expectExceptionMessage('An InjectDefinition with a value that cannot be serialized was provided.');

        $subject->serialize($containerDefinition);
    }

    public function testDeserializingConcreteServiceDefinition() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized($attribute = new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->type());
        self::assertEquals($attribute, $serviceDefinition->attribute());
        self::assertSame(['default'], $serviceDefinition->profiles());
        self::assertNull($serviceDefinition->name());
        self::assertFalse($serviceDefinition->isPrimary());
        self::assertTrue($serviceDefinition->isConcrete());
        self::assertFalse($serviceDefinition->isAbstract());
    }

    public function testDeserializingNamedConcreteServiceDefinition() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized($attribute = new Service(name: 'my_service_name'))}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->type());
        self::assertEquals($attribute, $serviceDefinition->attribute());
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
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized(new Service(primary: true))}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

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
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized($attribute = new Service(profiles: ['foo', 'bar', 'baz']))}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::singleConcreteService()->fooImplementation(), $serviceDefinition->type());
        self::assertEquals($attribute, $serviceDefinition->attribute());
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
    <serviceDefinition isConcrete="false">
      <type>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooInterface</type>
      <attribute>{$this->encodedAndSerialized($attribute = new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;
        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        $serviceDefinitions = $actual->serviceDefinitions();

        self::assertCount(1, $serviceDefinitions);
        $serviceDefinition = $serviceDefinitions[0];

        self::assertSame(Fixtures::implicitAliasedServices()->fooInterface(), $serviceDefinition->type());
        self::assertEquals($attribute, $serviceDefinition->attribute());
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
    <serviceDefinition isConcrete="false">
      <type>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooInterface</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooImplementation</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions>
    <aliasDefinition>
      <abstractService>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooInterface</abstractService>
      <concreteService>Cspray\AnnotatedContainer\Fixture\ImplicitAliasedServices\FooImplementation</concreteService>
    </aliasDefinition>
  </aliasDefinitions>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();
        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

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
    <serviceDefinition isConcrete="false">
      <type>Cspray\AnnotatedContainer\Fixture\InterfacePrepareServices\FooInterface</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions>
    <servicePrepareDefinition>
      <type>Cspray\AnnotatedContainer\Fixture\InterfacePrepareServices\FooInterface</type>
      <method>setBar</method>
      <attribute>{$this->encodedAndSerialized($attribute = new ServicePrepare())}</attribute>
    </servicePrepareDefinition>
  </servicePrepareDefinitions>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();
        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        self::assertCount(1, $actual->servicePrepareDefinitions());
        $prepareDefinition = $actual->servicePrepareDefinitions()[0];
        self::assertSame(Fixtures::interfacePrepareServices()->fooInterface(), $prepareDefinition->service());
        self::assertSame('setBar', $prepareDefinition->classMethod()->methodName());
        self::assertEquals($attribute, $prepareDefinition->attribute());
    }

    public function testDeserializeServiceDelegateDefinitions() : void {
        $version = AnnotatedContainerVersion::version();
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$version}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="false">
      <type>Cspray\AnnotatedContainer\Fixture\DelegatedService\ServiceInterface</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions>
    <serviceDelegateDefinition>
      <service>Cspray\AnnotatedContainer\Fixture\DelegatedService\ServiceInterface</service>
      <delegateType>Cspray\AnnotatedContainer\Fixture\DelegatedService\ServiceFactory</delegateType>
      <delegateMethod>createService</delegateMethod>
      <attribute>{$this->encodedAndSerialized($attribute = new ServiceDelegate())}</attribute>
    </serviceDelegateDefinition>
  </serviceDelegateDefinitions>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();
        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        self::assertCount(1, $actual->serviceDelegateDefinitions());
        $delegateDefinition = $actual->serviceDelegateDefinitions()[0];

        self::assertSame(Fixtures::delegatedService()->serviceInterface(), $delegateDefinition->service());
        self::assertSame(Fixtures::delegatedService()->serviceFactory(), $delegateDefinition->classMethod()->class());
        self::assertSame('createService', $delegateDefinition->classMethod()->methodName());
        self::assertEquals($attribute, $delegateDefinition->attribute());
    }

    public function testDeserializeInjectMethodParameter() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</class>
      <method>__construct</method>
      <parameter>val</parameter>
      <valueType>
        <type>string</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($attribute = new Inject('foobar'))}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->service()
        );
        self::assertEquals($attribute, $injectDefinition->attribute());
        self::assertSame('__construct', $injectDefinition->classMethodParameter()->methodName());
        self::assertSame('val', $injectDefinition->classMethodParameter()->parameterName());
        self::assertSame(types()->string(), $injectDefinition->classMethodParameter()->type());
        self::assertSame('foobar', $injectDefinition->value());
        self::assertSame(['default'], $injectDefinition->profiles());
        self::assertNull($injectDefinition->storeName());
    }

    public function testDeserializeInjectDefinitionUnitEnumValueMethodParameter() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectEnumConstructorServices\EnumInjector</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectEnumConstructorServices\EnumInjector</class>
      <method>__construct</method>
      <parameter>directions</parameter>
      <valueType>
        <type>Cspray\AnnotatedContainer\Fixture\InjectEnumConstructorServices\CardinalDirections</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($attribute = new Inject(CardinalDirections::West))}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertSame(
            Fixtures::injectEnumConstructorServices()->enumInjector(),
            $injectDefinition->service()
        );
        self::assertSame('__construct', $injectDefinition->classMethodParameter()->methodName());
        self::assertEquals($attribute, $injectDefinition->attribute());
        self::assertSame('directions', $injectDefinition->classMethodParameter()->parameterName());
        self::assertSame(types()->class(CardinalDirections::class), $injectDefinition->classMethodParameter()->type());
        self::assertSame(CardinalDirections::West, $injectDefinition->value());
        self::assertSame(['default'], $injectDefinition->profiles());
        self::assertNull($injectDefinition->storeName());
    }

    public function testDeserializeInjectDefinitionMethodParameterWithStore() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectCustomStoreServices\ScalarInjector</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectCustomStoreServices\ScalarInjector</class>
      <method>__construct</method>
      <parameter>key</parameter>
      <valueType>
        <type>string</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($attribute = new Inject('key', 'test-store'))}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertSame(
            Fixtures::injectCustomStoreServices()->scalarInjector(),
            $injectDefinition->service()
        );
        self::assertSame('__construct', $injectDefinition->classMethodParameter()->methodName());
        self::assertEquals($attribute, $injectDefinition->attribute());
        self::assertSame('key', $injectDefinition->classMethodParameter()->parameterName());
        self::assertSame(types()->string(), $injectDefinition->classMethodParameter()->type());
        self::assertSame('key', $injectDefinition->value());
        self::assertSame(['default'], $injectDefinition->profiles());
        self::assertSame('test-store', $injectDefinition->storeName());
    }

    public function testDeserializeInjectMethodWithProfiles() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="{$this->version()}">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</type>
      <attribute>{$this->encodedAndSerialized(new Service())}</attribute>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions>
    <injectDefinition>
      <class>Cspray\AnnotatedContainer\Fixture\InjectConstructorServices\StringInjectService</class>
      <method>__construct</method>
      <parameter>val</parameter>
      <valueType>
        <type>string</type>
      </valueType>
      <attribute>{$this->encodedAndSerialized($attribute = new Inject('annotated container', profiles: ['foo', 'baz']))}</attribute>
    </injectDefinition>
  </injectDefinitions>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));

        self::assertCount(1, $actual->injectDefinitions());
        $injectDefinition = $actual->injectDefinitions()[0];

        self::assertSame(
            Fixtures::injectConstructorServices()->injectStringService(),
            $injectDefinition->service()
        );
        self::assertEquals($attribute, $injectDefinition->attribute());
        self::assertSame('__construct', $injectDefinition->classMethodParameter()->methodName());
        self::assertSame('val', $injectDefinition->classMethodParameter()->parameterName());
        self::assertSame(types()->string(), $injectDefinition->classMethodParameter()->type());
        self::assertSame('annotated container', $injectDefinition->value());
        self::assertSame(['foo', 'baz'], $injectDefinition->profiles());
        self::assertNull($injectDefinition->storeName());
    }

    public function testDeserializeWithMismatchedVersionThrowsException() : void {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<annotatedContainerDefinition xmlns="https://annotated-container.cspray.io/schema/annotated-container-definition.xsd" version="not-up-to-date">
  <serviceDefinitions>
    <serviceDefinition isConcrete="true">
      <type>Cspray\AnnotatedContainer\Fixture\SingleConcreteService\FooImplementation</type>
      <attribute/>
    </serviceDefinition>
  </serviceDefinitions>
  <aliasDefinitions/>
  <servicePrepareDefinitions/>
  <serviceDelegateDefinitions/>
  <injectDefinitions/>
</annotatedContainerDefinition>

XML;

        $subject = new XmlContainerDefinitionSerializer();

        $this->expectException(MismatchedContainerDefinitionSerializerVersions::class);
        $this->expectExceptionMessage(sprintf(
            'The cached ContainerDefinition is from a version of Annotated Container, "not-up-to-date", that is not the ' .
            'currently installed version, "%s". Whenever Annotated Container is upgraded this cache must be ',
            AnnotatedContainerVersion::version()
        ));

        $actual = $subject->deserialize(SerializedContainerDefinition::fromString($xml));
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
            new Emitter()
        );

        $subject = new XmlContainerDefinitionSerializer();

        $containerDefinition = $compiler->analyze(
            ContainerDefinitionAnalysisOptionsBuilder::scanDirectories($fixture->getPath())->build()
        );

        $expected = $subject->serialize($containerDefinition);
        $actual = $subject->serialize($subject->deserialize($expected));

        self::assertSame($expected->asString(), $actual->asString());
    }

    public function testDeserializeWithSchemaNotValidatedThrowsException() : void {
        $subject = new XmlContainerDefinitionSerializer();

        $expected = <<<TEXT
The provided container definition does not validate against the schema.

Errors encountered:

- Start tag expected, '<' not found
- The document has no document element.

TEXT;


        $this->expectException(InvalidSerializedContainerDefinition::class);
        $this->expectExceptionMessage($expected);

        $subject->deserialize(
            SerializedContainerDefinition::fromString('not a valid xml schema')
        );
    }

    public function testLibxmlFunctionsResetProperly() : void {
        ThrowableAssert::assertThrows(fn() => (new XmlContainerDefinitionSerializer())->deserialize(
            SerializedContainerDefinition::fromString('not a valid xml schema')
        ));

        self::assertSame([], libxml_get_errors());
        self::assertFalse(libxml_use_internal_errors());
    }
}

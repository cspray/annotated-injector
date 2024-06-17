<?php

namespace Cspray\AnnotatedContainer\Definition\Serializer;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidSerializedContainerDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidInjectDefinition;
use Cspray\AnnotatedContainer\Exception\MismatchedContainerDefinitionSerializerVersions;
use Cspray\AnnotatedContainer\Internal\SerializerInjectValueParser;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Exception as PhpException;
use function Cspray\Typiphy\objectType;
use function PHPUnit\Framework\assertSame;

/**
 * @internal
 */
final class XmlContainerDefinitionSerializer implements ContainerDefinitionSerializer {

    private const XML_SCHEMA = 'https://annotated-container.cspray.io/schema/annotated-container-definition.xsd';

    private const ROOT_ELEMENT = 'annotatedContainerDefinition';

    private readonly SerializerInjectValueParser $injectValueParser;

    public function __construct() {
        $this->injectValueParser = new SerializerInjectValueParser();
    }

    public function serialize(ContainerDefinition $containerDefinition) : SerializedContainerDefinition {
        $dom = new DOMDocument(encoding: 'UTF-8');
        $dom->formatOutput = true;
        $root = $dom->createElementNS(self::XML_SCHEMA, self::ROOT_ELEMENT);
        $root->setAttribute('version', AnnotatedContainerVersion::version());

        $dom->appendChild($root);

        $this->addServiceDefinitionsToDom($root, $containerDefinition);
        $this->addAliasDefinitionsToDom($root, $containerDefinition);
        $this->addServicePrepareDefinitionsToDom($root, $containerDefinition);
        $this->addServiceDelegateDefinitionsToDom($root, $containerDefinition);
        $this->addInjectDefinitionsToDom($root, $containerDefinition);

        // if we get to this point then we know the XML document will contain _something_
        $xml = $dom->saveXML();
        assert($xml !== false && $xml !== '');

        return SerializedContainerDefinition::fromString($xml);
    }

    private function addServiceDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $serviceDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDefinitions')
        );

        foreach ($containerDefinition->serviceDefinitions() as $serviceDefinition) {
            $serviceDefinitionsNode->appendChild(
                $serviceDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDefinition')
            );

            if ($serviceDefinition->isPrimary()) {
                $serviceDefinitionNode->setAttribute('isPrimary', 'true');
            }

            $serviceDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'type', $serviceDefinition->type()->name())
            );
            $serviceDefinitionNode->appendChild(
                $nameNode = $dom->createElementNS(self::XML_SCHEMA, 'name')
            );

            $name = $serviceDefinition->name();
            if ($name !== null) {
                $nameNode->nodeValue = $name;
            }

            $serviceDefinitionNode->appendChild(
                $profilesNode = $dom->createElementNS(self::XML_SCHEMA, 'profiles')
            );

            foreach ($serviceDefinition->profiles() as $profile) {
                $profilesNode->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'profile', $profile)
                );
            }

            $serviceDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'concreteOrAbstract', $serviceDefinition->isConcrete() ? 'Concrete' : 'Abstract')
            );

            $serviceDefinitionNode->appendChild(
                $attrNode = $dom->createElementNS(self::XML_SCHEMA, 'attribute')
            );

            $attr = $serviceDefinition->attribute();
            if ($attr !== null) {
                $attrNode->nodeValue = base64_encode(serialize($attr));
            }
        }
    }

    private function addAliasDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $aliasDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'aliasDefinitions')
        );

        foreach ($containerDefinition->aliasDefinitions() as $aliasDefinition) {
            $aliasDefinitionsNode->appendChild(
                $aliasDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'aliasDefinition')
            );

            $aliasDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'abstractService', $aliasDefinition->abstractService()->name())
            );
            $aliasDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'concreteService', $aliasDefinition->concreteService()->name())
            );
        }
    }

    private function addServicePrepareDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $servicePrepareDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'servicePrepareDefinitions')
        );

        foreach ($containerDefinition->servicePrepareDefinitions() as $servicePrepareDefinition) {
            $servicePrepareDefinitionsNode->appendChild(
                $servicePrepareDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'servicePrepareDefinition')
            );

            $servicePrepareDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'type', $servicePrepareDefinition->service()->name())
            );

            $servicePrepareDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'method', $servicePrepareDefinition->methodName())
            );

            $servicePrepareDefinitionNode->appendChild(
                $attrNode = $dom->createElementNS(self::XML_SCHEMA, 'attribute')
            );

            $attr = $servicePrepareDefinition->attribute();
            if ($attr !== null) {
                $attrNode->nodeValue = base64_encode(serialize($attr));
            }
        }
    }

    private function addServiceDelegateDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $serviceDelegateDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDelegateDefinitions')
        );

        foreach ($containerDefinition->serviceDelegateDefinitions() as $serviceDelegateDefinition) {
            $serviceDelegateDefinitionsNode->appendChild(
                $serviceDelegateDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'serviceDelegateDefinition')
            );

            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'service', $serviceDelegateDefinition->serviceType()->name())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'delegateType', $serviceDelegateDefinition->delegateType()->name())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'delegateMethod', $serviceDelegateDefinition->delegateMethod())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $attrNode = $dom->createElementNS(self::XML_SCHEMA, 'attribute')
            );

            $attr = $serviceDelegateDefinition->attribute();
            if ($attr !== null) {
                $attrNode->nodeValue = base64_encode(serialize($attr));
            }
        }
    }

    private function addInjectDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $injectDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'injectDefinitions')
        );

        foreach ($containerDefinition->injectDefinitions() as $injectDefinition) {
            try {
                $serializedValue = serialize($injectDefinition->value());
            } catch (PhpException $exception) {
                throw InvalidInjectDefinition::fromValueNotSerializable($exception);
            }

            $dom = $root->ownerDocument;

            $injectDefinitionsNode->appendChild(
                $injectDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'injectDefinition')
            );

            $this->addMethodParameterInjectDefinitionToDom($injectDefinitionNode, $injectDefinition);

            $injectDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'valueType', base64_encode($injectDefinition->type()->name()))
            );

            $injectDefinitionNode->appendChild(
                $valueNode = $dom->createElementNS(self::XML_SCHEMA, 'value')
            );

            $valueNode->appendChild(
                $dom->createCDATASection(base64_encode($serializedValue))
            );

            $injectDefinitionNode->appendChild(
                $profilesNode = $dom->createElementNS(self::XML_SCHEMA, 'profiles')
            );

            foreach ($injectDefinition->profiles() as $profile) {
                $profilesNode->appendChild(
                    $dom->createElementNS(self::XML_SCHEMA, 'profile', $profile)
                );
            }

            $injectDefinitionNode->appendChild(
                $storeNode = $dom->createElementNS(self::XML_SCHEMA, 'store')
            );

            $injectDefinitionNode->appendChild(
                $attrNode = $dom->createElementNS(self::XML_SCHEMA, 'attribute')
            );

            $store = $injectDefinition->storeName();
            if ($store !== null) {
                $storeNode->nodeValue = $store;
            }

            $attr = $injectDefinition->attribute();
            if ($attr !== null) {
                $attrNode->nodeValue = base64_encode(serialize($attr));
            }
        }
    }

    private function addMethodParameterInjectDefinitionToDom(DOMElement $root, InjectDefinition $injectDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'class', $injectDefinition->class()->name())
        );

        $methodName = $injectDefinition->methodName();
        $root->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'method', $methodName)
        );

        $root->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'parameter', $injectDefinition->parameterName())
        );
    }

    public function deserialize(SerializedContainerDefinition $serializedContainerDefinition) : ContainerDefinition {
        $dom = new DOMDocument(encoding: 'UTF-8');

        // The assert() calls and docblock annotations in other methods, on values provided in the serialized container
        // definition are made because they are being asserted as part of the XML passing the container definition
        // schema below. If the code executes beyond the call to $dom->schemaValidate() then we can assume the stuff
        // covered by the schema definition is covered and we don't need to cover it again.

        libxml_use_internal_errors(true);
        try {
            $dom->loadXML($serializedContainerDefinition->asString());
            $schemaPath = dirname(__DIR__, 3) . '/annotated-container-definition.xsd';
            if (!$dom->schemaValidate($schemaPath)) {
                throw InvalidSerializedContainerDefinition::fromNotValidateXmlSchema(libxml_get_errors());
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('cd', self::XML_SCHEMA);

        $version = (string) $xpath->query('/cd:annotatedContainerDefinition/@version')[0]?->nodeValue;
        if ($version !== AnnotatedContainerVersion::version()) {
            throw MismatchedContainerDefinitionSerializerVersions::fromVersionIsNotInstalledAnnotatedContainerVersion($version);
        }

        $builder = ContainerDefinitionBuilder::newDefinition();

        $builder = $this->addServiceDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addAliasDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addServicePrepareDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addServiceDelegateDefinitionsToBuilder($builder, $xpath);
        $builder = $this->addInjectDefinitionsToBuilder($builder, $xpath);

        return $builder->build();
    }

    private function addServiceDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $serviceDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:serviceDefinitions/cd:serviceDefinition');
        assert($serviceDefinitions instanceof DOMNodeList);

        foreach ($serviceDefinitions as $serviceDefinition) {
            $serviceType = $xpath->query('cd:type/text()', $serviceDefinition)[0]->nodeValue;
            assert(class_exists($serviceType));

            $type = objectType($serviceType);

            $concreteOrAbstract = $xpath->query('cd:concreteOrAbstract/text()', $serviceDefinition)[0]->nodeValue;
            $isPrimary = $xpath->query('@isPrimary', $serviceDefinition)[0]?->nodeValue;
            if ($concreteOrAbstract === 'Concrete') {
                $serviceBuilder = ServiceDefinitionBuilder::forConcrete($type, $isPrimary === 'true');
            } else {
                $serviceBuilder = ServiceDefinitionBuilder::forAbstract($type);
            }

            $name = $xpath->query('cd:name/text()', $serviceDefinition)[0]?->nodeValue;
            if ($name !== null) {
                // We make several assertions that a name cannot be an empty string before a container is serialized and
                // a blank name should not be possible
                assert($name !== '');
                $serviceBuilder = $serviceBuilder->withName($name);
            }

            $profiles = $xpath->query('cd:profiles/cd:profile', $serviceDefinition);
            $serviceProfiles = [];
            foreach ($profiles as $profile) {
                $value = $profile->nodeValue;
                // The profileString type ensures that there is a value listed greater than 1
                assert($value !== null && $value !== '');
                $serviceProfiles[] = $value;
            }
            // The profilesType ensures there's at least 1 profile, additionally definitions are never assigned empty profiles
            assert($serviceProfiles !== []);

            $serviceBuilder = $serviceBuilder->withProfiles($serviceProfiles);

            $attr = $xpath->query('cd:attribute/text()', $serviceDefinition)[0]?->nodeValue;
            if ($attr !== null) {
                $serviceBuilder = $serviceBuilder->withAttribute(unserialize(base64_decode($attr)));
            }

            $builder = $builder->withServiceDefinition($serviceBuilder->build());
        }

        return $builder;
    }

    private function addAliasDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $aliasDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:aliasDefinitions/cd:aliasDefinition');
        assert($aliasDefinitions instanceof DOMNodeList);

        foreach ($aliasDefinitions as $aliasDefinition) {
            $abstract = $xpath->query('cd:abstractService/text()', $aliasDefinition)[0]->nodeValue;
            $concrete = $xpath->query('cd:concreteService/text()', $aliasDefinition)[0]->nodeValue;

            assert(class_exists($abstract));
            assert(class_exists($concrete));

            $builder = $builder->withAliasDefinition(
                AliasDefinitionBuilder::forAbstract(objectType($abstract))->withConcrete(objectType($concrete))->build()
            );
        }

        return $builder;
    }

    private function addServicePrepareDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $prepareDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:servicePrepareDefinitions/cd:servicePrepareDefinition');
        assert($prepareDefinitions instanceof DOMNodeList);

        foreach ($prepareDefinitions as $prepareDefinition) {
            $service = $xpath->query('cd:type/text()', $prepareDefinition)[0]->nodeValue;
            $method = $xpath->query('cd:method/text()', $prepareDefinition)[0]->nodeValue;

            assert(class_exists($service));
            assert($method !== null && $method !== '');

            $servicePrepareBuilder = ServicePrepareDefinitionBuilder::forMethod(objectType($service), $method);

            $attr = $xpath->query('cd:attribute/text()', $prepareDefinition)[0]?->nodeValue;
            if ($attr !== null) {
                $attrObject = unserialize(base64_decode($attr));
                assert($attrObject instanceof ServicePrepareAttribute);
                $servicePrepareBuilder = $servicePrepareBuilder->withAttribute($attrObject);
            }

            $builder = $builder->withServicePrepareDefinition($servicePrepareBuilder->build());
        }

        return $builder;
    }

    private function addServiceDelegateDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $delegateDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:serviceDelegateDefinitions/cd:serviceDelegateDefinition');
        assert($delegateDefinitions instanceof DOMNodeList);

        foreach ($delegateDefinitions as $delegateDefinition) {
            $service = $xpath->query('cd:service/text()', $delegateDefinition)[0]->nodeValue;
            $delegateType = $xpath->query('cd:delegateType/text()', $delegateDefinition)[0]->nodeValue;
            $delegateMethod = $xpath->query('cd:delegateMethod/text()', $delegateDefinition)[0]->nodeValue;

            assert(class_exists($service));
            assert(class_exists($delegateType));
            assert($delegateMethod !== null && $delegateMethod !== '');

            $serviceDelegateBuilder = ServiceDelegateDefinitionBuilder::forService(objectType($service))
                    ->withDelegateMethod(objectType($delegateType), $delegateMethod);

            $attr = $xpath->query('cd:attribute/text()', $delegateDefinition)[0]?->nodeValue;
            if ($attr !== null) {
                $serviceDelegateBuilder = $serviceDelegateBuilder->withAttribute(unserialize(base64_decode($attr)));
            }

            $builder = $builder->withServiceDelegateDefinition($serviceDelegateBuilder->build());
        }

        return $builder;
    }

    private function addInjectDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $injectDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:injectDefinitions/cd:injectDefinition');
        assert($injectDefinitions instanceof DOMNodeList);

        foreach ($injectDefinitions as $injectDefinition) {
            $valueType = $xpath->query('cd:valueType/text()', $injectDefinition)[0]->nodeValue;
            $store = $xpath->query('cd:store/text()', $injectDefinition)[0]?->nodeValue;
            $attr = $xpath->query('cd:attribute/text()', $injectDefinition)[0]?->nodeValue;
            $profiles = $xpath->query('cd:profiles/cd:profile/text()', $injectDefinition);
            $encodedSerializedValue = $xpath->query('cd:value/text()', $injectDefinition)[0]->nodeValue;

            $serializedValue = base64_decode($encodedSerializedValue);
            /** @var mixed $value */
            $value = unserialize($serializedValue);
            $valueType = $this->injectValueParser->convertStringToType(base64_decode($valueType));

            $type = $xpath->query('cd:class/text()', $injectDefinition)[0]->nodeValue;
            $methodName = $xpath->query('cd:method/text()', $injectDefinition)[0]->nodeValue;
            $parameter = $xpath->query('cd:parameter/text()', $injectDefinition)[0]->nodeValue;

            assert(class_exists($type));
            assert($methodName !== null && $methodName !== '');
            assert($parameter !== null && $parameter !== '');

            $injectBuilder = InjectDefinitionBuilder::forService(objectType($type))
                ->withMethod($methodName, $valueType, $parameter);

            $injectBuilder = $injectBuilder->withValue($value);

            $injectProfiles = [];
            foreach ($profiles as $profile) {
                $value = $profile->nodeValue;
                // The profileString type ensures that there is a value listed greater than 1
                assert($value !== null && $value !== '');
                $injectProfiles[] = $value;
            }
            // The profilesType ensures there's at least 1 profile, additionally definitions are never assigned empty profiles
            assert($injectProfiles !== []);

            $injectBuilder = $injectBuilder->withProfiles(...$injectProfiles);

            if ($store !== null) {
                assert($store !== '');
                $injectBuilder = $injectBuilder->withStore($store);
            }

            if ($attr !== null) {
                $injectBuilder = $injectBuilder->withAttribute(unserialize(base64_decode($attr)));
            }

            $builder = $builder->withInjectDefinition($injectBuilder->build());
        }

        return $builder;
    }
}

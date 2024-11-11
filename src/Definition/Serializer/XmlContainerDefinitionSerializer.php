<?php

namespace Cspray\AnnotatedContainer\Definition\Serializer;

use Cspray\AnnotatedContainer\AnnotatedContainerVersion;
use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidSerializedContainerDefinition;
use Cspray\AnnotatedContainer\Exception\InvalidInjectDefinition;
use Cspray\AnnotatedContainer\Exception\MismatchedContainerDefinitionSerializerVersions;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Exception as PhpException;
use function Cspray\AnnotatedContainer\Definition\definitionFactory;
use function Cspray\AnnotatedContainer\Reflection\types;

/**
 * @internal
 */
final class XmlContainerDefinitionSerializer implements ContainerDefinitionSerializer {

    private const XML_SCHEMA = 'https://annotated-container.cspray.io/schema/annotated-container-definition.xsd';

    private const ROOT_ELEMENT = 'annotatedContainerDefinition';

    public function serialize(ContainerDefinition $containerDefinition) : SerializedContainerDefinition {
        try {
            libxml_use_internal_errors(true);
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

            $this->validateDom($dom);

            // if we get to this point then we know the XML document will contain _something_
            $xml = $dom->saveXML();
            assert($xml !== false && $xml !== '');

            return SerializedContainerDefinition::fromString($xml);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
        }
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

            $serviceDefinitionNode->setAttribute('isConcrete', $serviceDefinition->isConcrete() ? 'true' : 'false');

            $serviceDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'type', $serviceDefinition->type()->name())
            );

            $serviceDefinitionNode->appendChild(
                $attrNode = $dom->createElementNS(self::XML_SCHEMA, 'attribute')
            );

            $attrNode->nodeValue = base64_encode(serialize($serviceDefinition->attribute()));
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
                $dom->createElementNS(self::XML_SCHEMA, 'method', $servicePrepareDefinition->classMethod()->methodName())
            );

            $servicePrepareDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'attribute', base64_encode(serialize($servicePrepareDefinition->attribute())))
            );
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
                $dom->createElementNS(self::XML_SCHEMA, 'service', $serviceDelegateDefinition->service()->name())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'delegateType', $serviceDelegateDefinition->classMethod()->class()->name())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'delegateMethod', $serviceDelegateDefinition->classMethod()->methodName())
            );
            $serviceDelegateDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'attribute', base64_encode(serialize($serviceDelegateDefinition->attribute())))
            );
        }
    }

    private function addInjectDefinitionsToDom(DOMElement $root, ContainerDefinition $containerDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $injectDefinitionsNode = $dom->createElementNS(self::XML_SCHEMA, 'injectDefinitions')
        );

        foreach ($containerDefinition->injectDefinitions() as $injectDefinition) {
            try {
                $serializedAttribute = base64_encode(serialize($injectDefinition->attribute()));
            } catch (PhpException $exception) {
                throw InvalidInjectDefinition::fromValueNotSerializable($exception);
            }

            $dom = $root->ownerDocument;

            $injectDefinitionsNode->appendChild(
                $injectDefinitionNode = $dom->createElementNS(self::XML_SCHEMA, 'injectDefinition')
            );

            $this->addMethodParameterInjectDefinitionToDom($injectDefinitionNode, $injectDefinition);

            $injectDefinitionNode->appendChild(
                $this->createAppropriateElementForValueType($dom, $injectDefinition)
            );

            $injectDefinitionNode->appendChild(
                $dom->createElementNS(self::XML_SCHEMA, 'attribute', $serializedAttribute)
            );
        }
    }

    private function createAppropriateElementForValueType(DOMDocument $dom, InjectDefinition $injectDefinition) : DOMElement {
        $valueType = $injectDefinition->classMethodParameter()->type();
        if ($valueType instanceof Type) {
            $valueTypeElement = $this->createTypeElement($dom, $valueType);
        } elseif ($valueType instanceof TypeUnion) {
            $valueTypeElement = $this->createTypeUnionElement($dom, $valueType);
        } elseif ($valueType instanceof TypeIntersect) {
            $valueTypeElement = $this->createTypeIntersectElement($dom, $valueType);
        }

        $valueTypeNode = $dom->createElementNS(self::XML_SCHEMA, 'valueType');
        $valueTypeNode->appendChild($valueTypeElement);

        return $valueTypeNode;
    }

    private function createTypeElement(DOMDocument $dom, Type $type) : DOMElement {
        return $dom->createElementNS(
            self::XML_SCHEMA,
            'type',
            $type->name(),
        );
    }

    private function createTypeUnionElement(DOMDocument $dom, TypeUnion $type) : DOMElement {
        $element = $dom->createElementNS(self::XML_SCHEMA, 'typeUnion');
        foreach ($type->types() as $typeOrTypeIntersect) {
            if ($typeOrTypeIntersect instanceof Type) {
                $element->appendChild($this->createTypeElement($dom, $typeOrTypeIntersect));
            } else {
                $element->appendChild($this->createTypeIntersectElement($dom, $typeOrTypeIntersect));
            }
        }

        return $element;
    }

    private function createTypeIntersectElement(DOMDocument $dom, TypeIntersect $type) : DOMElement {
        $valueTypeElement = $dom->createElementNS(
            self::XML_SCHEMA,
            'typeIntersect'
        );
        foreach ($type->types() as $t) {
            $valueTypeElement->appendChild($this->createTypeElement($dom, $t));
        }

        return $valueTypeElement;
    }

    private function addMethodParameterInjectDefinitionToDom(DOMElement $root, InjectDefinition $injectDefinition) : void {
        $dom = $root->ownerDocument;

        $root->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'class', $injectDefinition->classMethodParameter()->class()->name())
        );

        $methodName = $injectDefinition->classMethodParameter()->methodName();
        $root->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'method', $methodName)
        );

        $root->appendChild(
            $dom->createElementNS(self::XML_SCHEMA, 'parameter', $injectDefinition->classMethodParameter()->parameterName())
        );
    }

    private function validateDom(DOMDocument $dom) : void {
        $schemaPath = dirname(__DIR__, 3) . '/annotated-container-definition.xsd';
        if (!$dom->schemaValidate($schemaPath)) {
            throw InvalidSerializedContainerDefinition::fromNotValidateXmlSchema(libxml_get_errors());
        }
    }

    public function deserialize(SerializedContainerDefinition $serializedContainerDefinition) : ContainerDefinition {
        try {
            libxml_use_internal_errors(true);
            $dom = new DOMDocument(encoding: 'UTF-8');

            // The assert() calls and docblock annotations in other methods, on values provided in the serialized container
            // definition are made because they are being asserted as part of the XML passing the container definition
            // schema below. If the code executes beyond the call to $dom->schemaValidate() then we can assume the stuff
            // covered by the schema definition is covered and we don't need to cover it again.
            $dom->loadXML($serializedContainerDefinition->asString());

            $this->validateDom($dom);

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
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors(false);
        }
    }

    private function addServiceDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $serviceDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:serviceDefinitions/cd:serviceDefinition');
        assert($serviceDefinitions instanceof DOMNodeList);

        foreach ($serviceDefinitions as $serviceDefinition) {
            $serviceType = $xpath->query('cd:type/text()', $serviceDefinition)[0]->nodeValue;
            assert(class_exists($serviceType));
            $isConcrete = $xpath->query('@isConcrete', $serviceDefinition)[0]->nodeValue === 'true';
            $attr = unserialize(base64_decode(
                $xpath->query('cd:attribute/text()', $serviceDefinition)[0]?->nodeValue
            ));

            $builder = $builder->withServiceDefinition(
                definitionFactory()->serviceDefinitionFromManualSetup(
                    types()->class($serviceType),
                    $attr,
                    $isConcrete
                )
            );
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
                definitionFactory()->aliasDefinition(types()->class($abstract), types()->class($concrete))
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
            $attr = unserialize(base64_decode($xpath->query('cd:attribute/text()', $prepareDefinition)[0]?->nodeValue));

            assert(class_exists($service));
            assert($method !== null && $method !== '');

            $builder = $builder->withServicePrepareDefinition(
                definitionFactory()->servicePrepareDefinitionFromClassMethodAndAttribute(types()->class($service), $method, $attr)
            );
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
            $attr = unserialize(base64_decode($xpath->query('cd:attribute/text()', $delegateDefinition)[0]?->nodeValue));

            assert(class_exists($service));
            assert(class_exists($delegateType));
            assert($delegateMethod !== null && $delegateMethod !== '');

            $builder = $builder->withServiceDelegateDefinition(
                definitionFactory()->serviceDelegateDefinitionFromClassMethodAndAttribute(
                    types()->class($delegateType),
                    $delegateMethod,
                    $attr
                )
            );
        }

        return $builder;
    }

    private function addInjectDefinitionsToBuilder(ContainerDefinitionBuilder $builder, DOMXPath $xpath) : ContainerDefinitionBuilder {
        $injectDefinitions = $xpath->query('/cd:annotatedContainerDefinition/cd:injectDefinitions/cd:injectDefinition');
        assert($injectDefinitions instanceof DOMNodeList);

        foreach ($injectDefinitions as $injectDefinition) {
            $type = $xpath->query('cd:class/text()', $injectDefinition)[0]->nodeValue;
            $methodName = $xpath->query('cd:method/text()', $injectDefinition)[0]->nodeValue;
            $parameter = $xpath->query('cd:parameter/text()', $injectDefinition)[0]->nodeValue;
            $attr = $xpath->query('cd:attribute/text()', $injectDefinition)[0]?->nodeValue;
            $valueTypeName = $xpath->query('cd:valueType/*[1]', $injectDefinition)->item(0)->nodeName;

            assert(class_exists($type));
            assert($methodName !== null && $methodName !== '');
            assert($parameter !== null && $parameter !== '');

            if ($valueTypeName === 'type') {
                $valueType = types()->fromName($xpath->query('cd:valueType/cd:type/text()', $injectDefinition)->item(0)->nodeValue);
            } elseif ($valueTypeName === 'typeUnion') {
                $valueType = types()->union(
                    ...array_map(
                        static fn(DOMElement $domElement) => types()->fromName($domElement->nodeValue),
                        iterator_to_array($xpath->query('cd:valueType/cd:typeUnion/*', $injectDefinition))
                    )
                );
            } elseif ($valueTypeName === 'typeIntersect') {
                $valueType = types()->intersect(
                    ...array_map(
                        static fn(DOMElement $domElement) => types()->class($domElement->nodeValue),
                        iterator_to_array($xpath->query('cd:valueType/cd:typeIntersect/*', $injectDefinition))
                    )
                );
            }

            $attrInstance = unserialize(base64_decode($attr));
            assert($attrInstance instanceof InjectAttribute);

            $builder = $builder->withInjectDefinition(
                definitionFactory()->injectDefinitionFromManualSetup(
                    types()->fromName($type),
                    $methodName,
                    $valueType,
                    $parameter,
                    $attrInstance
                )
            );
        }

        return $builder;
    }
}

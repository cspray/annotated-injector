<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;
use Cspray\AnnotatedContainer\Exception\InvalidAnnotatedTarget;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegate;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\mixedType;
use function Cspray\Typiphy\nullType;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeIntersect;
use function Cspray\Typiphy\typeUnion;

/**
 * Responsible for converting an AnnotatedTarget into the appropriate definition object.
 */
final class AnnotatedTargetDefinitionConverter {

    /**
     * Parse the information from the provided $target and return a corresponding definition object.
     *
     * Generally speaking, the conversion process should not attempt to apply any domain logic to the result of the
     * definition. The logic around parsing these definitions into a Container can be complex and inter-dependent on
     * multiple definition types. As this converter intakes one $target at a time it does not have sufficient context
     * to perform any operations on the resultant definition.
     *
     * @param AnnotatedTarget $target
     * @return ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition|InjectDefinition
     */
    public function convert(AnnotatedTarget $target) : ServiceDefinition|ServicePrepareDefinition|ServiceDelegateDefinition|InjectDefinition {
        $attrInstance = $target->attributeInstance();
        if ($attrInstance instanceof ServiceAttribute) {
            return $this->buildServiceDefinition($target);
        }

        if ($attrInstance instanceof InjectAttribute) {
            return $this->buildInjectDefinition($target);
        }

        if ($attrInstance instanceof ServiceDelegateAttribute) {
            return $this->buildServiceDelegateDefinition($target);
        }

        if ($attrInstance instanceof ServicePrepareAttribute) {
            return $this->buildServicePrepareDefinition($target);
        }

        throw InvalidAnnotatedTarget::fromAttributeInstanceNotKnownType();
    }

    private function buildServiceDefinition(AnnotatedTarget $target) : ServiceDefinition {
        $reflection = $target->targetReflection();
        assert($reflection instanceof ReflectionClass);
        $serviceType = objectType($reflection->getName());
        /** @var Service $attribute */
        $attribute = $target->attributeInstance();
        if ($reflection->isInterface() || $reflection->isAbstract()) {
            $builder = ServiceDefinitionBuilder::forAbstract($serviceType);
        } else {
            $builder = ServiceDefinitionBuilder::forConcrete($serviceType, $attribute->isPrimary());
        }

        $builder = $builder->withAttribute($attribute);

        $profiles = $attribute->profiles();
        if ($profiles === []) {
            $profiles = ['default'];
        }
        $builder = $builder->withProfiles($profiles);
        $name = $attribute->name();
        if ($name !== null) {
            $builder = $builder->withName($name);
        }

        return $builder->build();
    }

    private function buildServiceDelegateDefinition(AnnotatedTarget $target) : ServiceDelegateDefinition {
        $reflection = $target->targetReflection();
        assert($reflection instanceof ReflectionMethod);
        $delegateType = $reflection->getDeclaringClass()->getName();
        $delegateMethod = $reflection->getName();
        $attribute = $target->attributeInstance();
        assert($attribute instanceof ServiceDelegateAttribute);

        /** @var class-string|null $service */
        $service = $attribute->service();
        if ($service !== null) {
            return ServiceDelegateDefinitionBuilder::forService(objectType($service))
                ->withDelegateMethod(objectType($delegateType), $delegateMethod)
                ->withAttribute($attribute)
                ->build();
        }

        $returnType = $reflection->getReturnType();
        if ($returnType instanceof ReflectionIntersectionType) {
            throw InvalidServiceDelegate::factoryMethodReturnsIntersectionType($delegateType, $delegateMethod);
        }

        if ($returnType instanceof ReflectionUnionType) {
            throw InvalidServiceDelegate::factoryMethodReturnsUnionType($delegateType, $delegateMethod);
        }

        if ($returnType instanceof ReflectionNamedType) {
            if (!class_exists($returnType->getName()) && !interface_exists($returnType->getName())) {
                throw InvalidServiceDelegate::factoryMethodReturnsScalarType($delegateType, $delegateMethod);
            }
            return ServiceDelegateDefinitionBuilder::forService(objectType($returnType->getName()))
                ->withDelegateMethod(objectType($delegateType), $delegateMethod)
                ->withAttribute($attribute)
                ->build();
        }

        throw InvalidServiceDelegate::factoryMethodDoesNotDeclareService($delegateType, $delegateMethod);
    }

    private function buildServicePrepareDefinition(AnnotatedTarget $target) : ServicePrepareDefinition {
        $reflection = $target->targetReflection();
        assert($reflection instanceof ReflectionMethod);
        $prepareType = $reflection->getDeclaringClass()->getName();
        $method = $reflection->getName();
        $attribute = $target->attributeInstance();
        assert($attribute instanceof ServicePrepareAttribute);

        return ServicePrepareDefinitionBuilder::forMethod(objectType($prepareType), $method)
            ->withAttribute($attribute)
            ->build();
    }

    private function buildInjectDefinition(AnnotatedTarget $target) : InjectDefinition {
        return $this->buildMethodInjectDefinition($target);
    }

    private function buildMethodInjectDefinition(AnnotatedTarget $target) : InjectDefinition {
        $targetReflection = $target->targetReflection();
        assert($targetReflection instanceof \ReflectionParameter);
        $declaringClass = $targetReflection->getDeclaringClass();
        assert(!is_null($declaringClass));

        $serviceType = objectType($declaringClass->getName());
        $method = $targetReflection->getDeclaringFunction()->getName();
        $param = $targetReflection->getName();
        $paramType = $this->convertReflectionType($targetReflection->getType());
        $attributeInstance = $target->attributeInstance();
        assert($attributeInstance instanceof InjectAttribute);

        $builder = InjectDefinitionBuilder::forService($serviceType)->withMethod($method, $paramType, $param);

        return $this->buildInjectFromAttributeData($builder, $attributeInstance);
    }

    private function buildInjectFromAttributeData(InjectDefinitionBuilder $builder, InjectAttribute $inject) : InjectDefinition {
        $builder = $builder->withAttribute($inject)->withValue($inject->value());
        $from = $inject->from();
        if ($from !== null) {
            $builder = $builder->withStore($from);
        }

        $profiles = $inject->profiles();
        if (count($profiles) === 0) {
            $profiles[] = 'default';
        }

        $builder = $builder->withProfiles(...$profiles);
        return $builder->build();
    }

    private function convertReflectionType(?ReflectionType $reflectionType) : Type|TypeUnion|TypeIntersect {
        if ($reflectionType instanceof ReflectionNamedType) {
            $paramType = $this->convertReflectionNamedType($reflectionType);
            // The ?type syntax is not recognized as a TypeUnion but we normalize it to use with our type system
            if ($paramType !== mixedType() && $reflectionType->allowsNull()) {
                $paramType = typeUnion($paramType, nullType());
            }
        } elseif ($reflectionType instanceof ReflectionUnionType || $reflectionType instanceof ReflectionIntersectionType) {
            $types = [];
            foreach ($reflectionType->getTypes() as $type) {
                $types[] = $this->convertReflectionNamedType($type);
            }
            if ($reflectionType instanceof ReflectionUnionType) {
                // At this point we know this is a non-empty list of types because we
                // encountered a union type. This might include scalar values so we can't
                // guarantee these are all object types.
                /** @var non-empty-list<Type> $types */
                $paramType = typeUnion(...$types);
            } else {
                // At this point we know this is a non-empty list of object types because
                // we encountered an intersection type so there must be something in the array
                // of converted types AND the only types allowed in a type intersect at the
                // language level are objects.
                /** @var non-empty-list<ObjectType> $types */
                $paramType = typeIntersect(...$types);
            }
        } else {
            $paramType = mixedType();
        }

        return $paramType;
    }

    private function convertReflectionNamedType(ReflectionNamedType $reflectionNamedType) : Type {
        $type = $reflectionNamedType->getName();
        $parsedType = match ($type) {
            'int' => intType(),
            'string' => stringType(),
            'bool' => boolType(),
            'array' => arrayType(),
            'float' => floatType(),
            'mixed' => mixedType(),
            default => null
        };

        if ($parsedType === null) {
            assert(class_exists($type));
            $parsedType = objectType($type);
        }

        return $parsedType;
    }
}

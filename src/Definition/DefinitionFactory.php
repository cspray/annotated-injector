<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Exception\InjectAttributeRequired;
use Cspray\AnnotatedContainer\Exception\ServiceAttributeRequired;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateAttributeRequired;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsIntersectionType;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsScalarType;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsUnionType;
use Cspray\AnnotatedContainer\Exception\ServiceDelegateReturnsUnknownType;
use Cspray\AnnotatedContainer\Exception\ServicePrepareAttributeRequired;
use Cspray\AnnotatedContainer\Exception\WrongTargetForInjectAttribute;
use Cspray\AnnotatedContainer\Exception\WrongTargetForServiceAttribute;
use Cspray\AnnotatedContainer\Exception\WrongTargetForServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Exception\WrongTargetForServicePrepareAttribute;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeFactory;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DefinitionFactory {

    private readonly TypeFactory $typeFactory;

    public function __construct() {
        $this->typeFactory = types();
    }

    public function serviceDefinitionFromAnnotatedTarget(AnnotatedTarget $annotatedTarget) : ServiceDefinition {
        $attribute = $annotatedTarget->attributeInstance();
        if (!$attribute instanceof ServiceAttribute) {
            throw ServiceAttributeRequired::fromNotServiceAttributeProvidedInAnnotatedTarget(
                $attribute::class
            );
        }

        $reflection = $annotatedTarget->targetReflection();
        if (!$reflection instanceof ReflectionClass) {
            throw WrongTargetForServiceAttribute::fromServiceAttributeNotTargetingClass($reflection);
        }

        return $this->serviceDefinitionFromReflectionClassAndAttribute($reflection, $attribute);
    }

    public function serviceDefinitionFromObjectTypeAndAttribute(
        Type $objectType,
        ServiceAttribute $serviceAttribute
    ) : ServiceDefinition {
        $reflection = new ReflectionClass($objectType->name());

        return $this->serviceDefinitionFromReflectionClassAndAttribute($reflection, $serviceAttribute);
    }

    private function serviceDefinitionFromReflectionClassAndAttribute(
        ReflectionClass $reflection,
        ServiceAttribute $attribute,
    ) : ServiceDefinition {
        $objectType = $this->typeFactory->class($reflection->getName());
        $isAbstract = $reflection->isInterface() || $reflection->isAbstract();

        return $this->serviceDefinitionFromManualSetup(
            $objectType,
            $attribute,
            !$isAbstract
        );
    }

    public function serviceDefinitionFromManualSetup(
        Type $type,
        ServiceAttribute $attribute,
        bool $isConcrete
    ) : ServiceDefinition {
        return new class($type, $attribute, $isConcrete) implements ServiceDefinition {

            public function __construct(
                private readonly Type $type,
                private readonly ServiceAttribute $attribute,
                private readonly bool $isConcrete,
            ) {
            }

            public function type() : Type {
                return $this->type;
            }

            public function name() : ?string {
                return $this->attribute->name();
            }

            public function profiles() : array {
                $profiles = $this->attribute->profiles();
                if ($profiles === []) {
                    $profiles = ['default'];
                }

                return $profiles;
            }

            public function isPrimary() : bool {
                return $this->attribute->isPrimary();
            }

            public function isConcrete() : bool {
                return $this->isConcrete;
            }

            public function isAbstract() : bool {
                return $this->isConcrete() === false;
            }

            public function attribute() : ServiceAttribute {
                return $this->attribute;
            }
        };
    }

    public function servicePrepareDefinitionFromAnnotatedTarget(AnnotatedTarget $annotatedTarget) : ServicePrepareDefinition {
        $attribute = $annotatedTarget->attributeInstance();
        if (!$attribute instanceof ServicePrepareAttribute) {
            throw ServicePrepareAttributeRequired::fromNotServicePrepareAttributeInAnnotatedTarget($attribute::class);
        }

        $reflection = $annotatedTarget->targetReflection();
        if (!$reflection instanceof ReflectionMethod) {
            throw WrongTargetForServicePrepareAttribute::fromServicePrepareAttributeNotTargetingMethod($reflection);
        }

        return $this->servicePrepareDefinitionFromReflectionMethodAndAttribute($reflection, $attribute);
    }

    public function servicePrepareDefinitionFromClassMethodAndAttribute(
        Type $objectType,
        string $method,
        ServicePrepareAttribute $attribute,
    ) : ServicePrepareDefinition {
        return new class($objectType, $method, $attribute) implements ServicePrepareDefinition {
            /**
             * @param Type $service
             * @param non-empty-string $method
             * @param ServicePrepareAttribute $attribute
             */
            public function __construct(
                private readonly Type $service,
                private readonly string $method,
                private readonly ServicePrepareAttribute $attribute,
            ) {
            }

            public function service() : Type {
                return $this->service;
            }

            /**
             * @return non-empty-string
             */
            public function methodName() : string {
                return $this->method;
            }

            public function attribute() : ServicePrepareAttribute {
                return $this->attribute;
            }
        };
    }

    private function servicePrepareDefinitionFromReflectionMethodAndAttribute(
        ReflectionMethod $reflection,
        ServicePrepareAttribute $attribute
    ) : ServicePrepareDefinition {
        return $this->servicePrepareDefinitionFromClassMethodAndAttribute(
            $this->typeFactory->class($reflection->getDeclaringClass()->getName()),
            $reflection->getName(),
            $attribute,
        );
    }

    public function serviceDelegateDefinitionFromAnnotatedTarget(AnnotatedTarget $target) : ServiceDelegateDefinition {
        $attribute = $target->attributeInstance();
        if (!$attribute instanceof ServiceDelegateAttribute) {
            throw ServiceDelegateAttributeRequired::fromNotServiceDelegateAttributeInAnnotatedTarget($attribute::class);
        }

        $reflection = $target->targetReflection();
        if (!$reflection instanceof ReflectionMethod) {
            throw WrongTargetForServiceDelegateAttribute::fromServiceDelegateAttributeNotTargetingMethod($reflection);
        }

        return $this->serviceDelegateDefinitionFromReflectionMethodAndAttribute($reflection, $attribute);
    }

    public function serviceDelegateDefinitionFromClassMethodAndAttribute(
        Type               $delegateType,
        string                   $delegateMethod,
        ServiceDelegateAttribute $attribute,
    ) : ServiceDelegateDefinition {
        $reflection = new ReflectionMethod($delegateType->name(), $delegateMethod);

        return $this->serviceDelegateDefinitionFromReflectionMethodAndAttribute($reflection, $attribute);
    }

    private function serviceDelegateDefinitionFromReflectionMethodAndAttribute(
        ReflectionMethod $reflection,
        ServiceDelegateAttribute $attribute
    ) : ServiceDelegateDefinition {
        $delegateType = $this->typeFactory->class($reflection->getDeclaringClass()->getName());
        $delegateMethod = $reflection->getName();
        $returnType = $reflection->getReturnType();

        if ($returnType === null) {
            throw ServiceDelegateReturnsUnknownType::fromServiceDelegateHasNoReturnType($delegateType, $delegateMethod);
        }

        if ($returnType instanceof ReflectionIntersectionType) {
            throw ServiceDelegateReturnsIntersectionType::fromServiceDelegateCreatesIntersectionType($delegateType, $delegateMethod);
        }

        if ($returnType instanceof ReflectionUnionType) {
            throw ServiceDelegateReturnsUnionType::fromServiceDelegateReturnsUnionType($delegateType, $delegateMethod);
        }

        $returnTypeName = $returnType->getName();
        if ($returnTypeName === 'self') {
            $returnTypeName = $delegateType->name();
        }

        if (!interface_exists($returnTypeName) && !class_exists($returnTypeName)) {
            throw ServiceDelegateReturnsScalarType::fromServiceDelegateCreatesScalarType($delegateType, $delegateMethod);
        }

        $serviceType = $this->typeFactory->class($returnTypeName);

        return new class(
            $delegateType,
            $delegateMethod,
            $serviceType,
            $attribute
        ) implements ServiceDelegateDefinition {

            public function __construct(
                private readonly Type $delegateType,
                private readonly string $delegateMethod,
                private readonly Type $serviceType,
                private readonly ServiceDelegateAttribute $attribute,
            ) {
            }

            public function delegateType() : Type {
                return $this->delegateType;
            }

            public function delegateMethod() : string {
                return $this->delegateMethod;
            }

            public function serviceType() : Type {
                return $this->serviceType;
            }

            public function attribute() : ServiceDelegateAttribute {
                return $this->attribute;
            }
        };
    }

    public function injectDefinitionFromAnnotatedTarget(AnnotatedTarget $target) : InjectDefinition {
        $attribute = $target->attributeInstance();
        if (!$attribute instanceof InjectAttribute) {
            throw InjectAttributeRequired::fromNotInjectAttributeProvidedInAnnotatedTarget($attribute::class);
        }

        $reflection = $target->targetReflection();
        if (!$reflection instanceof ReflectionParameter) {
            throw WrongTargetForInjectAttribute::fromInjectAttributeNotTargetMethodParameter($reflection);
        }

        return $this->injectDefinitionFromReflectionParameterAndAttribute($reflection, $attribute);
    }

    public function injectDefinitionFromReflectionParameterAndAttribute(
        ReflectionParameter $reflection,
        InjectAttribute $attribute,
    ) : InjectDefinition {
        $class = $this->typeFactory->class($reflection->getDeclaringClass()->getName());
        $method = $reflection->getDeclaringFunction()->getName();
        $type = $this->typeFactory->fromReflection($reflection->getType());
        $parameter = $reflection->getName();

        return $this->injectDefinitionFromManualSetup($class, $method, $type, $parameter, $attribute);
    }

    public function injectDefinitionFromManualSetup(
        Type $service,
        string $method,
        Type|TypeUnion|TypeIntersect $type,
        string $parameterName,
        InjectAttribute $injectAttribute
    ) : InjectDefinition {
        return new class(
            $service,
            $method,
            $type,
            $parameterName,
            $injectAttribute,
        ) implements InjectDefinition {

            public function __construct(
                private readonly Type $class,
                private readonly string $method,
                private readonly Type|TypeUnion|TypeIntersect $type,
                private readonly string $parameter,
                private readonly InjectAttribute $attribute,
            ) {
            }

            public function class() : Type {
                return $this->class;
            }

            public function methodName() : string {
                return $this->method;
            }

            public function type() : Type|TypeUnion|TypeIntersect {
                return $this->type;
            }

            public function parameterName() : string {
                return $this->parameter;
            }

            public function value() : mixed {
                return $this->attribute->value();
            }

            public function profiles() : array {
                $profiles = $this->attribute->profiles();
                return $profiles === [] ? ['default'] : $profiles;
            }

            public function storeName() : ?string {
                return $this->attribute->from();
            }

            public function attribute() : InjectAttribute {
                return $this->attribute;
            }
        };
    }

    public function aliasDefinition(Type $abstract, Type $concrete) : AliasDefinition {
        return new class($abstract, $concrete) implements AliasDefinition {
            public function __construct(
                private readonly Type $abstract,
                private readonly Type $concrete,
            ) {
            }

            public function abstractService() : Type {
                return $this->abstract;
            }

            public function concreteService() : Type {
                return $this->concrete;
            }
        };
    }
}

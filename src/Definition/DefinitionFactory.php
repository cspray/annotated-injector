<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Exception\InjectAttributeRequired;
use Cspray\AnnotatedContainer\Exception\InvalidReflectionParameterForInjectDefinition;
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
use Cspray\AnnotatedContainer\Profiles;
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
                    $profiles = [Profiles::DEFAULT_PROFILE];
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
        return new class($objectType, $this->classMethod($objectType, $method, false), $attribute) implements ServicePrepareDefinition {
            public function __construct(
                private readonly Type $service,
                private readonly ClassMethod $classMethod,
                private readonly ServicePrepareAttribute $attribute,
            ) {
            }

            #[Override]
            public function service() : Type {
                return $this->service;
            }

            #[Override]
            public function classMethod() : ClassMethod {
                return $this->classMethod;
            }

            #[Override]
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
        $name = $delegateType->name();
        assert(class_exists($name));
        $reflection = new ReflectionMethod($name, $delegateMethod);

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
            $serviceType,
            $this->classMethod($delegateType, $delegateMethod, $reflection->isStatic()),
            $attribute
        ) implements ServiceDelegateDefinition {

            public function __construct(
                private readonly Type $serviceType,
                private readonly ClassMethod $classMethod,
                private readonly ServiceDelegateAttribute $attribute,
            ) {
            }

            #[Override]
            public function service() : Type {
                return $this->serviceType;
            }

            #[Override]
            public function classMethod() : ClassMethod {
                return $this->classMethod;
            }

            public function profiles() : array {
                $profiles = $this->attribute->profiles();
                if ($profiles === []) {
                    $profiles = [Profiles::DEFAULT_PROFILE];
                }

                return $profiles;
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
        $declaringClass = $reflection->getDeclaringClass();
        if ($declaringClass === null) {
            throw InvalidReflectionParameterForInjectDefinition::fromReflectionParameterHasNoDeclaringClass();
        }
        $class = $this->typeFactory->class($declaringClass->getName());
        $method = $reflection->getDeclaringFunction()->getName();
        $type = $this->typeFactory->fromReflection($reflection->getType());
        $parameter = $reflection->getName();

        return $this->injectDefinitionFromManualSetup($class, $method, $type, $parameter, $attribute);
    }

    /**
     * @param Type $service
     * @param non-empty-string $method
     * @param Type|TypeUnion|TypeIntersect $type
     * @param non-empty-string $parameterName
     * @param InjectAttribute $injectAttribute
     * @return InjectDefinition
     */
    public function injectDefinitionFromManualSetup(
        Type $service,
        string $method,
        Type|TypeUnion|TypeIntersect $type,
        string $parameterName,
        InjectAttribute $injectAttribute
    ) : InjectDefinition {
        return new class(
            $service,
            $this->classMethodParameter(
                $service,
                $method,
                $type,
                $parameterName,
                false
            ),
            $injectAttribute,
        ) implements InjectDefinition {

            public function __construct(
                private readonly Type $service,
                private readonly ClassMethodParameter $classMethodParameter,
                private readonly InjectAttribute $attribute,
            ) {
            }

            #[Override]
            public function service() : Type {
                return $this->service;
            }

            #[Override]
            public function classMethodParameter() : ClassMethodParameter {
                return $this->classMethodParameter;
            }

            #[Override]
            public function value() : mixed {
                return $this->attribute->value();
            }

            #[Override]
            public function profiles() : array {
                $profiles = $this->attribute->profiles();
                return $profiles === [] ? [Profiles::DEFAULT_PROFILE] : $profiles;
            }

            #[Override]
            public function storeName() : ?string {
                return $this->attribute->from();
            }

            #[Override]
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

    /**
     * @param Type $class
     * @param non-empty-string $method
     * @return ClassMethod
     */
    private function classMethod(Type $class, string $method, bool $isStatic) : ClassMethod {
        return new class($class, $method, $isStatic) implements ClassMethod {

            public function __construct(
                private readonly Type $class,
                private readonly string $method,
                private readonly bool $isStatic,
            ) {
            }

            public function class() : Type {
                return $this->class;
            }

            public function methodName() : string {
                return $this->method;
            }

            public function isStatic() : bool {
                return $this->isStatic;
            }
        };
    }

    private function classMethodParameter(
        Type $class,
        string $method,
        Type|TypeUnion|TypeIntersect $type,
        string $parameter,
        bool $isStatic
    ) : ClassMethodParameter {
        return new class($class, $method, $type, $parameter, $isStatic) implements ClassMethodParameter {

            public function __construct(
                private readonly Type $class,
                private readonly string $method,
                private readonly Type|TypeUnion|TypeIntersect $type,
                private readonly string $parameter,
                private readonly bool $isStatic,
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

            public function isStatic() : bool {
                return $this->isStatic;
            }
        };
    }
}

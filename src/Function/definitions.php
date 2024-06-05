<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinitionBuilder;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;
use ReflectionClass;
use ReflectionException;

/**
 * @param ObjectType $type
 * @param string|null $name
 * @param list<non-empty-string> $profiles
 * @param bool $isPrimary
 * @return ServiceDefinition
 * @throws ReflectionException
 */
function service(ObjectType $type, ?string $name = null, array $profiles = [], bool $isPrimary = false) : ServiceDefinition {
    $typeName = $type->getName();
    $reflection = new ReflectionClass($typeName);
    $methodArgs = [$type];
    $method = $reflection->isAbstract() || $reflection->isInterface() ? 'forAbstract' : 'forConcrete';
    /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
    if ($method === 'forConcrete') {
        $methodArgs[] = $isPrimary;
    }
    /** @var ServiceDefinitionBuilder $serviceDefinitionBuilder */
    $serviceDefinitionBuilder = ServiceDefinitionBuilder::$method(...$methodArgs);
    if (isset($name)) {
        $serviceDefinitionBuilder = $serviceDefinitionBuilder->withName($name);
    }

    if (empty($profiles)) {
        $profiles[] = 'default';
    }
    $serviceDefinitionBuilder = $serviceDefinitionBuilder->withProfiles($profiles);

    return $serviceDefinitionBuilder->build();
}

function alias(ObjectType $abstract, ObjectType $concrete) : AliasDefinition {
    return AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete)->build();
}

function serviceDelegate(ObjectType $service, ObjectType $factoryClass, string $factoryMethod) : ServiceDelegateDefinition {
    return ServiceDelegateDefinitionBuilder::forService($service)
        ->withDelegateMethod($factoryClass, $factoryMethod)->build();
}

function servicePrepare(ObjectType $service, string $method) : ServicePrepareDefinition {
    return ServicePrepareDefinitionBuilder::forMethod($service, $method)->build();
}

function inject(ObjectType $service, string $method, string $paramName, Type|TypeUnion|TypeIntersect $type, mixed $value, array $profiles = [], string $from = null) : InjectDefinition {
    $injectDefinitionBuilder = InjectDefinitionBuilder::forService($service)
        ->withMethod($method, $type, $paramName)
        ->withValue($value);

    if (!empty($profiles)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withProfiles(...$profiles);
    }

    if (isset($from)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withStore($from);
    }

    return $injectDefinitionBuilder->build();
}

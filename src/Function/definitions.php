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
 * @param DefinitionProviderContext $context
 * @param ObjectType $type
 * @param string|null $name
 * @param list<non-empty-string> $profiles
 * @param bool $isPrimary
 * @return ServiceDefinition
 * @throws ReflectionException
 */
function service(DefinitionProviderContext $context, ObjectType $type, ?string $name = null, array $profiles = [], bool $isPrimary = false) : ServiceDefinition {
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

    $serviceDefinition = $serviceDefinitionBuilder->build();
    $context->setBuilder($context->getBuilder()->withServiceDefinition($serviceDefinition));
    return $serviceDefinition;
}

function alias(DefinitionProviderContext $context, ObjectType $abstract, ObjectType $concrete) : AliasDefinition {
    $aliasDefinition = AliasDefinitionBuilder::forAbstract($abstract)->withConcrete($concrete)->build();
    $context->setBuilder($context->getBuilder()->withAliasDefinition($aliasDefinition));
    return $aliasDefinition;
}

function serviceDelegate(DefinitionProviderContext $context, ObjectType $service, ObjectType $factoryClass, string $factoryMethod) : ServiceDelegateDefinition {
    $serviceDelegateDefinition = ServiceDelegateDefinitionBuilder::forService($service)->withDelegateMethod($factoryClass, $factoryMethod)->build();
    $context->setBuilder($context->getBuilder()->withServiceDelegateDefinition($serviceDelegateDefinition));
    return $serviceDelegateDefinition;
}

function servicePrepare(DefinitionProviderContext $context, ObjectType $service, string $method) : ServicePrepareDefinition {
    $servicePrepareDefinition = ServicePrepareDefinitionBuilder::forMethod($service, $method)->build();
    $context->setBuilder($context->getBuilder()->withServicePrepareDefinition($servicePrepareDefinition));
    return $servicePrepareDefinition;
}

function injectMethodParam(DefinitionProviderContext $context, ObjectType $service, string $method, string $paramName, Type|TypeUnion|TypeIntersect $type, mixed $value, array $profiles = [], string $from = null) : InjectDefinition {
    $injectDefinitionBuilder = InjectDefinitionBuilder::forService($service)
        ->withMethod($method, $type, $paramName)
        ->withValue($value);

    if (!empty($profiles)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withProfiles(...$profiles);
    }

    if (isset($from)) {
        $injectDefinitionBuilder = $injectDefinitionBuilder->withStore($from);
    }

    $injectDefinition = $injectDefinitionBuilder->build();
    $context->setBuilder($context->getBuilder()->withInjectDefinition($injectDefinition));
    return $injectDefinition;
}

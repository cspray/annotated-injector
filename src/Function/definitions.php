<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;
use Cspray\AnnotatedContainer\Internal\ServiceDelegateFromFunctionalApi;
use Cspray\AnnotatedContainer\Internal\ServiceFromFunctionalApi;
use Cspray\AnnotatedContainer\Internal\ServicePrepareFromFunctionalApi;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\Reflection\TypeIntersect;
use Cspray\AnnotatedContainer\Reflection\TypeUnion;

/**
 * @param Type $type
 * @param non-empty-string|null $name
 * @param list<non-empty-string> $profiles
 * @param bool $isPrimary
 * @return ServiceDefinition
 */
function service(Type $type, ?string $name = null, array $profiles = [], bool $isPrimary = false) : ServiceDefinition {
    return definitionFactory()->serviceDefinitionFromObjectTypeAndAttribute(
        $type,
        new ServiceFromFunctionalApi($profiles, $isPrimary, $name)
    );
}

/**
 * @param Type $factoryClass
 * @param non-empty-string $factoryMethod
 * @param list<non-empty-string> $profiles
 */
function serviceDelegate(Type $factoryClass, string $factoryMethod, array $profiles = []) : ServiceDelegateDefinition {
    return definitionFactory()->serviceDelegateDefinitionFromClassMethodAndAttribute(
        $factoryClass,
        $factoryMethod,
        new ServiceDelegateFromFunctionalApi($profiles)
    );
}

/**
 * @param Type $service
 * @param non-empty-string $method
 * @return ServicePrepareDefinition
 */
function servicePrepare(Type $service, string $method) : ServicePrepareDefinition {
    return definitionFactory()->servicePrepareDefinitionFromClassMethodAndAttribute(
        $service,
        $method,
        new ServicePrepareFromFunctionalApi()
    );
}

/**
 * @param Type $service
 * @param non-empty-string $method
 * @param non-empty-string $paramName
 * @param Type|TypeUnion|TypeIntersect $type
 * @param mixed $value
 * @param list<non-empty-string> $profiles
 * @param non-empty-string|null $from
 * @return InjectDefinition
 */
function inject(
    Type $service,
    string $method,
    string $paramName,
    Type|TypeUnion|TypeIntersect $type,
    mixed $value,
    array $profiles = [],
    string $from = null
) : InjectDefinition {
    return definitionFactory()->injectDefinitionFromManualSetup(
        $service,
        $method,
        $type,
        $paramName,
        new Inject($value, $from, $profiles)
    );
}

function definitionFactory() : DefinitionFactory {
    /** @var ?DefinitionFactory $factory */
    static $factory = null;
    $factory ??= new DefinitionFactory();
    return $factory;
}

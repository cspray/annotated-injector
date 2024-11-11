<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\State;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\ListOf;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\ClassMethodParameter;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\MultipleInjectOnSameParameter;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\Reflection\Type;
use UnitEnum;

/**
 * @internal
 */
final class ContainerFactoryState {

    /**
     * @var list<InjectDefinition>
     */
    private readonly array $injectDefinitions;

    public function __construct(
        private readonly ContainerDefinition $containerDefinition,
        private readonly Profiles $profiles,
        private readonly AliasDefinitionResolver $aliasDefinitionResolver,
        /**
         * @var array<non-empty-string, ParameterStore>
         */
        private readonly array $parameterStores,
    ) {
        $resolvedInjects = [];
        foreach ($this->containerDefinition->injectDefinitions() as $injectDefinition) {
            $resolvedInjects[] = $this->injectDefinitionWithResolvableValue($injectDefinition);
        }
        $this->injectDefinitions = $resolvedInjects;
    }

    private function injectDefinitionWithResolvableValue(InjectDefinition $injectDefinition) : InjectDefinition {
        $value = $injectDefinition->value();
        $store = $injectDefinition->storeName();
        $type = $injectDefinition->classMethodParameter()->type();
        if ($store !== null) {
            $parameterStore = $this->parameterStores[$store] ?? null;
            if ($parameterStore === null) {
                throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($store);
            }
            assert(is_string($value) && $value !== '');

            /** @var mixed $value */
            $value = new ValueFetchedFromParameterStore($parameterStore, $type, $value);
        }

        if ($value instanceof ListOf) {
            $value = new ServiceCollectorReference($value, $value->type(), $type);
        } elseif ((class_exists($type->name()) || interface_exists($type->name())) && !is_a($type->name(), UnitEnum::class, true)) {
            assert(is_string($value) && $value !== '');
            $value = new ContainerReference($value, $type);
        }

        return new class($injectDefinition, $value) implements InjectDefinition {

            public function __construct(
                private readonly InjectDefinition $injectDefinition,
                private readonly mixed $value,
            ) {
            }

            public function value() : mixed {
                return $this->value;
            }

            public function profiles() : array {
                return $this->injectDefinition->profiles();
            }

            public function storeName() : ?string {
                return $this->injectDefinition->storeName();
            }

            public function attribute() : InjectAttribute {
                return $this->injectDefinition->attribute();
            }

            public function service() : Type {
                return $this->injectDefinition->service();
            }

            public function classMethodParameter() : ClassMethodParameter {
                return $this->injectDefinition->classMethodParameter();
            }
        };
    }

    public function activeProfiles() : Profiles {
        return $this->profiles;
    }

    /**
     * @return list<ServiceDefinition>
     */
    public function serviceDefinitions() : array {
        return $this->containerDefinition->serviceDefinitions();
    }

    /**
     * @return list<ServicePrepareDefinition>
     */
    public function servicePrepareDefinitions() : array {
        return $this->containerDefinition->servicePrepareDefinitions();
    }

    /**
     * @return list<ServiceDelegateDefinition>
     */
    public function serviceDelegateDefinitions() : array {
        return $this->containerDefinition->serviceDelegateDefinitions();
    }

    public function resolveAliasDefinitionForAbstractService(ServiceDefinition $serviceDefinition) : ?Type {
        return $this->aliasDefinitionResolver->resolveAlias(
            $this->containerDefinition,
            $this->profiles,
            $serviceDefinition->type()
        )->aliasDefinition()?->concreteService();
    }

    /**
     * @param ServiceDefinition $serviceDefinition
     * @return list<InjectDefinition>
     */
    public function constructorInjectDefinitionsForServiceDefinition(ServiceDefinition $serviceDefinition) : array {
        return $this->prioritizeInjectDefinitions($serviceDefinition->type(), '__construct');
    }

    /**
     * @param ServicePrepareDefinition $servicePrepareDefinition
     * @return list<InjectDefinition>
     */
    public function injectDefinitionsForServicePrepareDefinition(ServicePrepareDefinition $servicePrepareDefinition) : array {
        return $this->prioritizeInjectDefinitions($servicePrepareDefinition->service(), $servicePrepareDefinition->classMethod()->methodName());
    }

    /**
     * @param ServiceDelegateDefinition $serviceDelegateDefinition
     * @return list<InjectDefinition>
     */
    public function injectDefinitionsForServiceDelegateDefinition(ServiceDelegateDefinition $serviceDelegateDefinition) : array {
        return array_values(array_filter(
            $this->injectDefinitions,
            static fn(InjectDefinition $i) => $i->service()->equals($serviceDelegateDefinition->classMethod()->class()) &&
                $i->classMethodParameter()->methodName() === $serviceDelegateDefinition->classMethod()->methodName()
        ));
    }

    /**
     * @param Type $type
     * @param non-empty-string $method
     * @return list<InjectDefinition>
     */
    private function prioritizeInjectDefinitions(Type $type, string $method) : array {
        /**
         * @var array<non-empty-string, array<int, list<InjectDefinition>> $prioritizedParams
         */
        $prioritizedParams = [];
        foreach ($this->injectDefinitions as $injectDefinition) {
            if (!$injectDefinition->service()->equals($type) || $injectDefinition->classMethodParameter()->methodName() !== $method) {
                continue;
            }

            $parameter = $injectDefinition->classMethodParameter()->parameterName();
            $prioritizedParams[$parameter] ??= [];

            $score = $this->profiles->priorityScore($injectDefinition->profiles());
            $prioritizedParams[$parameter][$score] ??= [];
            $prioritizedParams[$parameter][$score][] = $injectDefinition;
        }

        $params = [];
        foreach ($prioritizedParams as $param => $scoredInjectDefinitions) {
            $paramScore = max(array_keys($scoredInjectDefinitions));
            if (count($scoredInjectDefinitions[$paramScore]) > 1) {
                throw MultipleInjectOnSameParameter::fromClassMethodParamHasMultipleInject(
                    $type->name(),
                    $method,
                    $param
                );
            }

            $params[] = $scoredInjectDefinitions[$paramScore][0];
        }

        return $params;
    }

    /**
     * @param ServiceDefinition $serviceDefinition
     * @return list<ServicePrepareDefinition>
     */
    public function servicePrepareDefinitionsForServiceDefinition(ServiceDefinition $serviceDefinition) : array {
        return array_values(array_filter(
            $this->servicePrepareDefinitions(),
            static fn(ServicePrepareDefinition $sp) => $sp->service()->equals($serviceDefinition->type())
        ));
    }

    public function serviceDelegateDefinitionForServiceDefinition(ServiceDefinition $serviceDefinition) : ?ServiceDelegateDefinition {
        foreach ($this->containerDefinition->serviceDelegateDefinitions() as $serviceDelegateDefinition) {
            if ($serviceDelegateDefinition->service()->equals($serviceDefinition->type())) {
                return $serviceDelegateDefinition;
            }
        }

        return null;
    }

    public function typeForServiceName(string $name) : ?Type {
        foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->name() === $name) {
                return $serviceDefinition->type();
            }
        }

        return null;
    }
}

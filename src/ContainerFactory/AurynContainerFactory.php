<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\InjectionException;
use Auryn\Injector;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerFactoryState;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\InjectParameterValue;
use Cspray\AnnotatedContainer\ContainerFactory\State\ServiceCollectorReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\ValueFetchedFromParameterStore;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!class_exists(Injector::class)) {
    throw new \RuntimeException("To enable the AurynContainerFactory please install rdlowrey/auryn!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd

/**
 * A ContainerFactory that utilizes the rdlowrey/auryn Container as its backing implementation.
 *
 * @extends AbstractContainerFactory<Injector, Injector>
 */
final class AurynContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    protected function createAnnotatedContainer(ContainerFactoryState $state) : AnnotatedContainer {
        $injector = new Injector();

        $this->addServiceDefinitionsToInjector($state, $injector);
        $this->addServiceDelegateDefinitionsToInjector($state, $injector);

        return new class($injector, $state) implements AnnotatedContainer {

            public function __construct(
                private readonly Injector $injector,
                private readonly ContainerFactoryState $state,
            ) {
                $this->injector->delegate(AutowireableFactory::class, fn() => $this);
                $this->injector->delegate(AutowireableInvoker::class, fn() => $this);
                $this->injector->delegate(Profiles::class, fn() => $this->state->activeProfiles());
            }

            /**
             * @template T
             * @param class-string<T>|non-empty-string $id
             * @return ($id is class-string<T> ? T : mixed)
             */
            public function get(string $id) {
                try {
                    if (!$this->has($id)) {
                        throw ServiceNotFound::fromServiceNotInContainer($id);
                    }

                    assert($id !== '');

                    $namedType = $this->state->typeForServiceName($id) ?? null;
                    if ($namedType !== null) {
                        $id = $namedType->name();
                    }

                    /** @var T|mixed $value */
                    $value = $this->injector->make($id);
                    return $value;
                } catch (InjectionException $injectionException) {
                    throw ContainerException::fromCaughtThrowable($injectionException);
                }
            }

            public function has(string $id): bool {
                assert($id !== '');

                $namedType = $this->state->typeForServiceName($id) ?? null;
                if ($namedType !== null) {
                    return true;
                }

                $anyDefined = 0;
                foreach ($this->injector->inspect($id) as $definitions) {
                    $anyDefined += count($definitions);
                }
                return $anyDefined > 0;
            }

            /**
             * @template T of object
             * @psalm-param class-string<T> $classType
             * @psalm-return T
             */
            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                /** @var T $object */
                $object = $this->injector->make(
                    $classType,
                    $this->convertAutowireableParameterSet($parameters)
                );

                return $object;
            }

            public function backingContainer() : Injector {
                return $this->injector;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->injector->execute(
                    $callable,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            private function convertAutowireableParameterSet(AutowireableParameterSet $parameters = null) : array {
                /** @var array<non-empty-string, mixed> $params */
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        if ($parameter->isServiceIdentifier()) {
                            $parameterValue = $parameter->value();

                            /** @var non-empty-string $value */
                            $value = $parameterValue->name();
                            $name = $parameter->name();
                        } else {
                            /** @var mixed $value */
                            $value = $parameter->value();
                            $name = ':' . $parameter->name();
                        }

                        $params[$name] = $value;
                    }
                }
                return $params;
            }
        };
    }

    private function addServiceDefinitionsToInjector(ContainerFactoryState $state, Injector $injector) : void {
        foreach ($state->serviceDefinitions() as $serviceDefinition) {
            $injector->share($serviceDefinition->type()->name());

            if ($serviceDefinition->isAbstract()) {
                $aliasedType = $state->resolveAliasDefinitionForAbstractService($serviceDefinition);
                if ($aliasedType !== null) {
                    $injector->alias($serviceDefinition->type()->name(), $aliasedType->name());
                }
            }

            $constructorParams = $this->parametersForServiceConstructorToArray($injector, $state, $serviceDefinition);
            if ($constructorParams !== []) {
                $injector->define($serviceDefinition->type()->name(), $constructorParams);
            }

            $servicePrepares = $state->servicePrepareDefinitionsForServiceDefinition($serviceDefinition);
            if ($servicePrepares !== []) {
                $injector->prepare(
                    $serviceDefinition->type()->name(),
                    function(object $object) use($state, $injector, $servicePrepares) : void {
                        foreach ($servicePrepares as $servicePrepareDefinition) {
                            $injector->execute(
                                [$object, $servicePrepareDefinition->classMethod()->methodName()],
                                $this->parametersForServicePrepareToArray($injector, $state, $servicePrepareDefinition)
                            );
                        }
                    }
                );
            }
        }
    }

    private function addServiceDelegateDefinitionsToInjector(ContainerFactoryState $state, Injector $injector) : void {
        foreach ($state->serviceDelegateDefinitions() as $serviceDelegateDefinition) {
            $injector->delegate(
                $serviceDelegateDefinition->service()->name(),
                function() use($injector, $state, $serviceDelegateDefinition) : object {
                    return $injector->execute(
                        [$serviceDelegateDefinition->classMethod()->class()->name(), $serviceDelegateDefinition->classMethod()->methodName()],
                        $this->parametersForServiceDelegateToArray($injector, $state, $serviceDelegateDefinition),
                    );
                }
            );
        }
    }

    protected function resolveParameterForInjectDefinition(
        object                $containerBuilder,
        ContainerFactoryState $state,
        InjectDefinition      $definition,
    ) : InjectParameterValue {
        $key = $definition->classMethodParameter()->parameterName();
        $value = $definition->value();
        if ($value instanceof ContainerReference) {
            $nameType = $state->typeForServiceName($value->name);
            $value = $nameType === null ? $value->name : $nameType->name();
        } elseif ($value instanceof ServiceCollectorReference) {
            $key = '+' . $key;
            $value = fn() => $this->serviceCollectorReferenceToListOfServices($containerBuilder, $state, $definition, $value);
        } elseif ($value instanceof ValueFetchedFromParameterStore) {
            $key = '+' . $key;
            $value = static fn() : mixed => $value->get();
        } else {
            $key = ':' . $key;
        }

        return new InjectParameterValue($key, $value);
    }

    protected function retrieveServiceFromIntermediaryContainer(object $container, ServiceDefinition $definition) : object {
        return $container->make($definition->type()->name());
    }
}

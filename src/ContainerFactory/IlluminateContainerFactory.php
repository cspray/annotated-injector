<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Closure;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerFactoryState;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\InjectParameterValue;
use Cspray\AnnotatedContainer\ContainerFactory\State\ServiceCollectorReference;
use Cspray\AnnotatedContainer\ContainerFactory\State\ValueFetchedFromParameterStore;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\Reflection\Type;
use Illuminate\Contracts\Container\Container;
use function Cspray\AnnotatedContainer\Reflection\types;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!interface_exists(Container::class)) {
    throw new \RuntimeException("To enable the IlluminateContainerFactory please install illuminate/container 10+!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd

/**
 * @extends AbstractContainerFactory<Container, Container>
 */
final class IlluminateContainerFactory extends AbstractContainerFactory {

    protected function createAnnotatedContainer(ContainerFactoryState $state) : AnnotatedContainer {
        $container = new \Illuminate\Container\Container();

        foreach ($state->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->isAbstract()) {
                $aliasedType = $state->resolveAliasDefinitionForAbstractService($serviceDefinition);
                if ($aliasedType !== null) {
                    $container->singleton($serviceDefinition->type()->name(), $aliasedType->name());
                }
            } else {
                $container->singleton($serviceDefinition->type()->name());
            }

            $name = $serviceDefinition->name();
            if ($name !== null) {
                $container->alias($serviceDefinition->type()->name(), $name);
            }

            foreach ($this->parametersForServiceConstructorToArray($container, $state, $serviceDefinition) as $key => $value) {
                $container->when($serviceDefinition->type()->name())->needs($key)->give($value);
            }

            $servicePrepares = $state->servicePrepareDefinitionsForServiceDefinition($serviceDefinition);
            if ($servicePrepares !== []) {
                $container->afterResolving($serviceDefinition->type()->name(), function(object $object) use($state, $servicePrepares, $container) : void {
                    foreach ($servicePrepares as $servicePrepare) {
                        $container->call(
                            [$object, $servicePrepare->classMethod()->methodName()],
                            array_map(static fn(Closure $closure) => $closure(), $this->parametersForServicePrepareToArray($container, $state, $servicePrepare)),
                        );
                    }
                });
            }
        }

        foreach ($state->serviceDelegateDefinitions() as $serviceDelegateDefinition) {
            $container->singleton(
                $serviceDelegateDefinition->service()->name(),
                function (Container $container) use($serviceDelegateDefinition, $state) : object {
                    if ($serviceDelegateDefinition->classMethod()->isStatic()) {
                        $target = $serviceDelegateDefinition->classMethod()->class()->name();
                    } else {
                        $target = $container->get($serviceDelegateDefinition->classMethod()->class()->name());
                    }

                    return $container->call(
                        [$target, $serviceDelegateDefinition->classMethod()->methodName()],
                        array_map(static fn(Closure $closure) => $closure(), $this->parametersForServiceDelegateToArray($container, $state, $serviceDelegateDefinition)),
                    );
                }
            );
        }

        $container->instance(Profiles::class, $state->activeProfiles());

        return new class($container) implements AnnotatedContainer {

            public function __construct(
                private readonly Container $container,
            ) {
                $this->container->instance(AutowireableFactory::class, $this);
                $this->container->instance(AutowireableInvoker::class, $this);
            }

            public function backingContainer() : Container {
                return $this->container;
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                $object = $this->container->make($classType, $this->resolvedParameters($parameters));
                assert($object instanceof $classType);
                return $object;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->container->call($callable, $this->resolvedParameters($parameters));
            }

            /**
             * @return array<non-empty-string, mixed>
             */
            private function resolvedParameters(?AutowireableParameterSet $parameters) : array {
                /** @var array<non-empty-string, mixed> $params */
                $params = [];
                if ($parameters !== null) {
                    foreach ($parameters as $parameter) {
                        if ($parameter->isServiceIdentifier()) {
                            $parameterValue = $parameter->value();
                            assert($parameterValue instanceof Type);

                            /** @psalm-var mixed $value */
                            $value = $this->container->get($parameterValue->name());
                        } else {
                            /** @psalm-var mixed $value */
                            $value = $parameter->value();
                        }

                        $params[$parameter->name()] = $value;
                    }
                }

                return $params;
            }

            /**
             * @template T
             * @param class-string<T>|non-empty-string $id
             * @return ($id is class-string<T> ? T : mixed)
             */
            public function get(string $id) {
                if (!$this->has($id)) {
                    throw ServiceNotFound::fromServiceNotInContainer($id);
                }

                /** @var T|mixed $object */
                $object = $this->container->get($id);
                return $object;
            }

            public function has(string $id) : bool {
                return $this->container->has($id);
            }
        };
    }

    protected function resolveParameterForInjectDefinition(object $containerBuilder, ContainerFactoryState $state, InjectDefinition $definition,) : InjectParameterValue {
        $key = sprintf('%s', $definition->classMethodParameter()->parameterName());
        if ($definition->classMethodParameter()->methodName() === '__construct') {
            $key = '$' . $key;
        }
        $value = $definition->value();
        if ($value instanceof ContainerReference) {
            $key = $definition->classMethodParameter()->type()->name();
            $value = fn() => $containerBuilder->get($value->name);
        } elseif ($value instanceof ServiceCollectorReference) {
            if (!$value->collectionType->equals(types()->array())) {
                $key = $value->collectionType->name();
            }
            $value = fn() => $this->serviceCollectorReferenceToListOfServices($containerBuilder, $state, $definition, $value);
        } else {
            $value = fn() : mixed => $value instanceof ValueFetchedFromParameterStore ? $value->get() : $value;
        }

        return new InjectParameterValue($key, $value);
    }

    protected function retrieveServiceFromIntermediaryContainer(object $container, ServiceDefinition $definition) : object {
        return $container->get($definition->type()->name());
    }
}

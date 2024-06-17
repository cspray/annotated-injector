<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\ContainerFactoryEmitter;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\Typiphy\ObjectType;
use Illuminate\Contracts\Container\Container;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\objectType;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!interface_exists(Container::class)) {
    throw new \RuntimeException("To enable the IlluminateContainerFactory please install illuminate/container 10+!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd

final class IlluminateContainerFactory extends AbstractContainerFactory {

    public function __construct(
        ContainerFactoryEmitter $emitter,
        private readonly Container $container = new \Illuminate\Container\Container(),
        AliasDefinitionResolver $aliasDefinitionResolver = new StandardAliasDefinitionResolver(),

    ) {
        parent::__construct($emitter, $aliasDefinitionResolver);
    }

    protected function containerFactoryState(ContainerDefinition $containerDefinition) : ContainerFactoryState {
        return new IlluminateContainerFactoryState($this->container, $containerDefinition);
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        if ($definition->isConcrete()) {
            $state->addConcreteService($definition->type()->name());
        } else {
            $state->addAbstractService($definition->type()->name());
        }
        $name = $definition->name();
        if ($name !== null) {
            $state->addNamedService($definition->type()->name(), $name);
        }
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $definition = $resolution->aliasDefinition();
        if ($definition !== null) {
            $state->addAlias($definition->abstractService()->name(), $definition->concreteService()->name());
        }
    }

    protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);

        $reflectionMethod = new \ReflectionMethod($definition->delegateType()->name(), $definition->delegateMethod());
        if ($reflectionMethod->isStatic()) {
            $state->addStaticDelegate(
                $definition->serviceType()->name(),
                $definition->delegateType()->name(),
                $definition->delegateMethod()
            );
        } else {
            $state->addInstanceDelegate(
                $definition->serviceType()->name(),
                $definition->delegateType()->name(),
                $definition->delegateMethod()
            );
        }
    }

    protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $state->addServicePrepare($definition->service()->name(), $definition->methodName());
    }

    protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $state->addMethodInject(
            $definition->class()->name(),
            $definition->methodName(),
            $definition->parameterName(),
            $this->injectDefinitionValue($definition)
        );
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, Profiles $activeProfiles) : AnnotatedContainer {
        assert($state instanceof IlluminateContainerFactoryState);
        $container = $state->container;


        foreach ($state->aliases() as $abstract => $concrete) {
            $container->singleton($abstract, $concrete);
        }

        foreach ($state->delegates() as $service => $delegateInfo) {
            if ($delegateInfo['isStatic']) {
                $target = $delegateInfo['delegateType'];
            } else {
                $target = $container->get($delegateInfo['delegateType']);
            }
            $container->singleton(
                $service,
                static fn(Container $container) : object => $container->call([$target, $delegateInfo['delegateMethod']])
            );
        }

        foreach ($state->namedServices() as $service => $name) {
            $container->alias($service, $name);
        }

        foreach ($state->concreteServices() as $service) {
            $container->singleton($service);
        }

        $container->afterResolving(static function ($created, Container $container) use($state) {
            foreach ($state->servicePrepares() as $service => $methods) {
                if ($created instanceof $service) {
                    foreach ($methods as $method) {
                        $params = [];
                        foreach ($state->parametersForMethod($service, $method) as $param => $value) {
                            $params[$param] = $value instanceof ContainerReference ? $container->get($value->name) : $value;
                        }
                        $container->call([$created, $method], $params);
                    }
                    break;
                }
            }
        });

        foreach ($state->methodInject() as $service => $methods) {
            foreach ($methods as $method => $params) {
                if ($method === '__construct') {
                    /** @var mixed $value */
                    foreach ($params as $param => $value) {
                        if ($value instanceof ContainerReference) {
                            $container->when($service)
                                ->needs($value->type->name())
                                ->give($value->name);
                        } elseif ($value instanceof ServiceCollectorReference) {
                            if ($value->collectionType === arrayType()) {
                                $paramIdentifier = sprintf('$%s', $param);
                            } else {
                                $paramIdentifier = $value->collectionType->name();
                            }

                            $container->when($service)
                                ->needs($paramIdentifier)
                                ->give(function() use($state, $container, $value, $service): mixed {
                                    $values = [];
                                    foreach ($state->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                                        if ($serviceDefinition->isAbstract() || $serviceDefinition->type()->name() === $service) {
                                            continue;
                                        }

                                        if (is_a($serviceDefinition->type()->name(), $value->valueType->name(), true)) {
                                            $values[] = $container->get($serviceDefinition->type()->name());
                                        }
                                    }
                                    return $value->listOf->toCollection($values);
                                });
                        } else {
                            $container->when($service)
                                ->needs(sprintf('$%s', $param))
                                ->give(static fn() : mixed => $value);
                        }
                    }
                }
            }
        }

        foreach ($state->abstractServices() as $abstractService) {
            $container->singletonIf($abstractService);
        }

        $container->instance(Profiles::class, $activeProfiles);

        return new class($state) implements AnnotatedContainer {

            public function __construct(
                private readonly IlluminateContainerFactoryState $state,
            ) {
                $this->state->container->instance(AutowireableFactory::class, $this);
                $this->state->container->instance(AutowireableInvoker::class, $this);
            }

            public function backingContainer() : Container {
                return $this->state->container;
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                $object = $this->state->container->make($classType, $this->resolvedParameters($parameters));
                assert($object instanceof $classType);
                return $object;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->state->container->call($callable, $this->resolvedParameters($parameters));
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
                            assert($parameterValue instanceof ObjectType);

                            /** @psalm-var mixed $value */
                            $value = $this->state->container->get($parameterValue->name());
                        } else {
                            /** @psalm-var mixed $value */
                            $value = $parameter->value();
                        }

                        $params[$parameter->name()] = $value;
                    }
                }

                return $params;
            }

            public function get(string $id) {
                if (!$this->has($id)) {
                    throw ServiceNotFound::fromServiceNotInContainer($id);
                }

                return $this->state->container->get($id);
            }

            public function has(string $id) : bool {
                return $this->state->container->has($id);
            }
        };
    }
}

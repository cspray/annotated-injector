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

    protected function backingContainerType() : ObjectType {
        return objectType(\Illuminate\Container\Container::class);
    }

    protected function containerFactoryState(ContainerDefinition $containerDefinition) : ContainerFactoryState {
        return new IlluminateContainerFactoryState($this->container, $containerDefinition);
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        if ($definition->isConcrete()) {
            $state->addConcreteService($definition->type()->getName());
        } else {
            $state->addAbstractService($definition->type()->getName());
        }
        $name = $definition->name();
        if ($name !== null) {
            $state->addNamedService($definition->type()->getName(), $name);
        }
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $definition = $resolution->aliasDefinition();
        if ($definition !== null) {
            $state->addAlias($definition->abstractService()->getName(), $definition->concreteService()->getName());
        }
    }

    protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);

        $reflectionMethod = new \ReflectionMethod($definition->delegateType()->getName(), $definition->delegateMethod());
        if ($reflectionMethod->isStatic()) {
            $state->addStaticDelegate(
                $definition->serviceType()->getName(),
                $definition->delegateType()->getName(),
                $definition->delegateMethod()
            );
        } else {
            $state->addInstanceDelegate(
                $definition->serviceType()->getName(),
                $definition->delegateType()->getName(),
                $definition->delegateMethod()
            );
        }
    }

    protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $state->addServicePrepare($definition->service()->getName(), $definition->methodName());
    }

    protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $state->addMethodInject(
            $definition->targetIdentifier()->class()->getName(),
            $definition->targetIdentifier()->methodName(),
            $definition->targetIdentifier()->name(),
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
                static fn(Container $container) => $container->call([$target, $delegateInfo['delegateMethod']])
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
                    foreach ($params as $param => $value) {
                        if ($value instanceof ContainerReference) {
                            $container->when($service)
                                ->needs($value->type->getName())
                                ->give($value->name);
                        } elseif ($value instanceof ServiceCollectorReference) {
                            if ($value->collectionType === arrayType()) {
                                $paramIdentifier = sprintf('$%s', $param);
                            } else {
                                $paramIdentifier = $value->collectionType->getName();
                            }

                            $container->when($service)
                                ->needs($paramIdentifier)
                                ->give(function() use($state, $container, $value) {
                                    $values = [];
                                    foreach ($state->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                                        if ($serviceDefinition->isAbstract()) {
                                            continue;
                                        }

                                        if (is_a($serviceDefinition->type()->getName(), $value->valueType->getName(), true)) {
                                            $values[] = $container->get($serviceDefinition->type()->getName());
                                        }
                                    }
                                    return $value->listOf->toCollection($values);
                                });
                        } else {
                            $container->when($service)
                                ->needs(sprintf('$%s', $param))
                                ->give($value);
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

            public function getBackingContainer() : Container {
                return $this->state->container;
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                $params = [];
                if ($parameters !== null) {
                    foreach ($parameters as $parameter) {
                        $value = $parameter->value();
                        if ($parameter->isServiceIdentifier()) {
                            $value = $this->state->container->get($value->getName());
                        }
                        $params[$parameter->name()] = $value;
                    }
                }
                return $this->state->container->make($classType, $params);
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                $params = [];
                if ($parameters !== null) {
                    foreach ($parameters as $parameter) {
                        $value = $parameter->value();
                        if ($parameter->isServiceIdentifier()) {
                            $value = $this->state->container->get($value->getName());
                        }
                        $params[$parameter->name()] = $value;
                    }
                }
                return $this->state->container->call($callable, $params);
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

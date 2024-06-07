<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\Typiphy\ObjectType;
use Illuminate\Contracts\Container\Container;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\objectType;

final class IlluminateContainerFactory extends AbstractContainerFactory {

    public function __construct(
        private readonly Container $container = new \Illuminate\Container\Container(),
        AliasDefinitionResolver $aliasDefinitionResolver = null
    ) {
        parent::__construct($aliasDefinitionResolver);
    }

    protected function getBackingContainerType() : ObjectType {
        return objectType(\Illuminate\Container\Container::class);
    }

    protected function getContainerFactoryState(ContainerDefinition $containerDefinition) : ContainerFactoryState {
        return new IlluminateContainerFactoryState($this->container, $containerDefinition);
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        if ($definition->isConcrete()) {
            $state->addConcreteService($definition->getType()->getName());
        } else {
            $state->addAbstractService($definition->getType()->getName());
        }
        $name = $definition->getName();
        if ($name !== null) {
            $state->addNamedService($definition->getType()->getName(), $name);
        }
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $definition = $resolution->getAliasDefinition();
        if ($definition !== null) {
            $state->addAlias($definition->getAbstractService()->getName(), $definition->getConcreteService()->getName());
        }
    }

    protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);

        $reflectionMethod = new \ReflectionMethod($definition->getDelegateType()->getName(), $definition->getDelegateMethod());
        if ($reflectionMethod->isStatic()) {
            $state->addStaticDelegate(
                $definition->getServiceType()->getName(),
                $definition->getDelegateType()->getName(),
                $definition->getDelegateMethod()
            );
        } else {
            $state->addInstanceDelegate(
                $definition->getServiceType()->getName(),
                $definition->getDelegateType()->getName(),
                $definition->getDelegateMethod()
            );
        }
    }

    protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $state->addServicePrepare($definition->getService()->getName(), $definition->getMethod());
    }

    protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        if ($definition->getTargetIdentifier()->isMethodParameter()) {
            $state->addMethodInject(
                $definition->getTargetIdentifier()->getClass()->getName(),
                $definition->getTargetIdentifier()->getMethodName(),
                $definition->getTargetIdentifier()->getName(),
                $this->getInjectDefinitionValue($definition)
            );
        } else {
            $state->addPropertyInject(
                $definition->getTargetIdentifier()->getClass()->getName(),
                $definition->getTargetIdentifier()->getName(),
                $this->getInjectDefinitionValue($definition)
            );
        }
    }

    protected function handleConfigurationDefinition(ContainerFactoryState $state, ConfigurationDefinition $definition) : void {
        assert($state instanceof IlluminateContainerFactoryState);
        $state->addConcreteService($definition->getClass()->getName());
        $name = $definition->getName();
        if ($name !== null) {
            $state->addNamedService($definition->getClass()->getName(), $name);
        }
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, ActiveProfiles $activeProfiles) : AnnotatedContainer {
        assert($state instanceof IlluminateContainerFactoryState);
        $container = $state->container;


        foreach ($state->getAliases() as $abstract => $concrete) {
            $container->singleton($abstract, $concrete);
        }

        foreach ($state->getDelegates() as $service => $delegateInfo) {
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

        foreach ($state->getNamedServices() as $service => $name) {
            $container->alias($service, $name);
        }

        foreach ($state->getConcreteServices() as $service) {
            $container->singleton($service);
        }

        $container->afterResolving(static function ($created, Container $container) use($state) {
            foreach ($state->getServicePrepares() as $service => $methods) {
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

            foreach ($state->getPropertyInject() as $service => $properties) {
                foreach ($properties as $property => $value) {
                    $reflectionProperty = new \ReflectionProperty($service, $property);
                    $reflectionProperty->setValue($created, $value);
                }
            }
        });

        foreach ($state->getMethodInject() as $service => $methods) {
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
                                ->give(function() use($state, $container, $value, $service): mixed {
                                    $values = [];
                                    foreach ($state->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                                        if ($serviceDefinition->isAbstract() || $serviceDefinition->getType()->getName() === $service) {
                                            continue;
                                        }

                                        if (is_a($serviceDefinition->getType()->getName(), $value->valueType->getName(), true)) {
                                            $values[] = $container->get($serviceDefinition->getType()->getName());
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

        foreach ($state->getAbstractServices() as $abstractService) {
            $container->singletonIf($abstractService);
        }

        $container->instance(ActiveProfiles::class, $activeProfiles);

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
                        $value = $parameter->getValue();
                        if ($parameter->isServiceIdentifier()) {
                            $value = $this->state->container->get($value->getName());
                        }
                        $params[$parameter->getName()] = $value;
                    }
                }
                return $this->state->container->make($classType, $params);
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                $params = [];
                if ($parameters !== null) {
                    foreach ($parameters as $parameter) {
                        $value = $parameter->getValue();
                        if ($parameter->isServiceIdentifier()) {
                            $value = $this->state->container->get($value->getName());
                        }
                        $params[$parameter->getName()] = $value;
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

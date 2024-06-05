<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameter;
use Cspray\AnnotatedContainer\Autowire\AutowireableParameterSet;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Profiles;
use DI\Container;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\Typiphy\ObjectType;
use DI\ContainerBuilder;
use function Cspray\Typiphy\objectType;
use function DI\decorate;
use function DI\get;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!class_exists(Container::class)) {
    throw new \RuntimeException("To enable the PhpDiContainerFactory please install php-di/php-di 7+!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd


/**
 * A ContainerFactory that utilizes the php-di/php-di library.
 */
final class PhpDiContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    protected function backingContainerType() : ObjectType {
        return objectType(Container::class);
    }

    protected function containerFactoryState(ContainerDefinition $containerDefinition) : ContainerFactoryState {
        return new PhpDiContainerFactoryState($containerDefinition);
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $serviceType = $definition->type()->getName();
        $state->addService($serviceType);
        $state->autowireService($serviceType);
        $key = $serviceType;
        $name = $definition->name();
        if ($name !== null) {
            $state->addService($name);
            $state->referenceService($name, $definition->type()->getName());
            $key = $name;
        }
        $state->setServiceKey($serviceType, $key);
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $aliasDefinition = $resolution->aliasDefinition();
        if ($aliasDefinition !== null) {
            $state->referenceService(
                $state->serviceKey($aliasDefinition->abstractService()->getName()),
                $aliasDefinition->concreteService()->getName()
            );
        }
    }

    public function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $serviceName = $definition->serviceType()->getName();
        $state->factoryService($serviceName, static fn(Container $container) => $container->call(
            [$definition->delegateType()->getName(), $definition->delegateMethod()]
        ));
    }

    public function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);

        $state->addServicePrepare($definition->service()->getName(), $definition->methodName());
    }

    public function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void {
        assert($state instanceof PhpDiContainerFactoryState);
        $state->addMethodInject(
            $definition->targetIdentifier()->class()->getName(),
            $definition->targetIdentifier()->methodName(),
            $definition->targetIdentifier()->name(),
            $this->injectDefinitionValue($definition)
        );
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, Profiles $activeProfiles) : AnnotatedContainer {
        assert($state instanceof PhpDiContainerFactoryState);
        $containerBuilder = new ContainerBuilder();

        $definitions = $state->definitions();

        foreach ($state->methodInject() as $service => $methods) {
            foreach ($methods as $method => $params) {
                if ($method === '__construct') {
                    foreach ($params as $param => $value) {
                        $definitions[$service]->constructorParameter($param, $value);
                    }
                }
            }
        }

        $servicePrepareDefinitions = [];
        foreach ($state->servicePrepares() as $service => $methods) {
            $servicePrepareDefinitions[$service] = decorate(static function (object $service, Container $container) use($state, $methods) {
                foreach ($methods as $method) {
                    $params = $state->parametersForMethod($service::class, $method);
                    $container->call([$service, $method], $params);
                }
                return $service;
            });
        }

        $containerBuilder->addDefinitions($definitions);
        $containerBuilder->addDefinitions($servicePrepareDefinitions);

        return new class($containerBuilder->build(), $state->services(), $activeProfiles) implements AnnotatedContainer {

            public function __construct(
                private readonly Container $container,
                private readonly array $serviceTypes,
                Profiles $activeProfiles
            ) {
                $this->container->set(AutowireableFactory::class, $this);
                $this->container->set(AutowireableInvoker::class, $this);
                $this->container->set(Profiles::class, $activeProfiles);
            }

            public function make(string $classType, AutowireableParameterSet $parameters = null) : object {
                return $this->container->make(
                    $classType,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            public function get(string $id) {
                if (!$this->has($id)) {
                    throw ServiceNotFound::fromServiceNotInContainer($id);
                }
                return $this->container->get($id);
            }

            public function has(string $id) : bool {
                return in_array($id, $this->serviceTypes);
            }

            public function backingContainer() : Container {
                return $this->container;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->container->call(
                    $callable,
                    $this->convertAutowireableParameterSet($parameters)
                );
            }

            private function convertAutowireableParameterSet(AutowireableParameterSet $parameters = null) : array {
                $params = [];
                if (!is_null($parameters)) {
                    /** @var AutowireableParameter $parameter */
                    foreach ($parameters as $parameter) {
                        $params[$parameter->name()] = $parameter->isServiceIdentifier() ? get($parameter->value()->getName()) : $parameter->value();
                    }
                }
                return $params;
            }
        };
    }
}

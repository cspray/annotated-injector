<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\InjectionException;
use Auryn\Injector;
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
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\ServiceNotFound;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

// @codeCoverageIgnoreStart
// phpcs:disable
if (!class_exists(Injector::class)) {
    throw new \RuntimeException("To enable the AurynContainerFactory please install rdlowrey/auryn!");
}
// phpcs:enable
// @codeCoverageIgnoreEnd

/**
 * A ContainerFactory that utilizes the rdlowrey/auryn Container as its backing implementation.
 */
final class AurynContainerFactory extends AbstractContainerFactory implements ContainerFactory {

    protected function containerFactoryState(ContainerDefinition $containerDefinition) : AurynContainerFactoryState {
        return new AurynContainerFactoryState($containerDefinition);
    }

    protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void {
        assert($state instanceof AurynContainerFactoryState);
        $state->injector->share($definition->type()->name());
        $name = $definition->name();
        if ($name !== null) {
            $state->addNameType($name, $definition->type());
        }
    }

    protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void {
        assert($state instanceof AurynContainerFactoryState);
        $alias = $resolution->aliasDefinition();
        if ($alias !== null) {
            $state->injector->alias(
                $alias->abstractService()->name(),
                $alias->concreteService()->name()
            );
        }
    }

    protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void {
        assert($state instanceof AurynContainerFactoryState);
        $delegateType = $definition->delegateType()->name();
        $delegateMethod = $definition->delegateMethod();

        $parameters = $state->parametersForMethod($delegateType, $delegateMethod);
        $state->injector->delegate(
            $definition->serviceType()->name(),
            static fn() : mixed => $state->injector->execute([$delegateType, $delegateMethod], $parameters)
        );
    }

    protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void {
        assert($state instanceof AurynContainerFactoryState);
        $serviceType = $definition->service()->name();

        $state->addServicePrepare($serviceType, $definition->methodName());
    }

    protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void {
        assert($state instanceof AurynContainerFactoryState);
        $injectTargetType = $definition->class()->name();
        $method = $definition->methodName();
        $parameterName = $definition->parameterName();
        /** @var mixed $value */
        $value = $this->injectDefinitionValue($definition);

        $state->addMethodInject($injectTargetType, $method, $parameterName, $value);
    }

    protected function createAnnotatedContainer(ContainerFactoryState $state, Profiles $activeProfiles) : AnnotatedContainer {
        assert($state instanceof AurynContainerFactoryState);

        foreach ($state->methodInject() as $service => $methods) {
            if (array_key_exists('__construct', $methods)) {
                $state->injector->define($service, $methods['__construct']);
            }
        }

        foreach ($state->servicePrepares() as $serviceType => $methods) {
            $state->injector->prepare(
                $serviceType,
                static function(object $object) use($state, $methods) : void {
                    foreach ($methods as $method) {
                        $params = $state->parametersForMethod($object::class, $method);
                        $state->injector->execute([$object, $method], $params);
                    }
                }
            );
        }

        return new class($state, $activeProfiles) implements AnnotatedContainer {

            public function __construct(
                private readonly AurynContainerFactoryState $state,
                Profiles $activeProfiles
            ) {
                $state->injector->delegate(AutowireableFactory::class, fn() => $this);
                $state->injector->delegate(AutowireableInvoker::class, fn() => $this);
                $state->injector->delegate(Profiles::class, fn() => $activeProfiles);
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

                    $namedType = $this->state->typeForName($id);
                    if ($namedType !== null) {
                        $id = $namedType->name();
                    }

                    /** @var T|mixed $value */
                    $value = $this->state->injector->make($id);
                    return $value;
                } catch (InjectionException $injectionException) {
                    throw ContainerException::fromCaughtThrowable($injectionException);
                }
            }

            public function has(string $id): bool {
                assert($id !== '');

                $namedType = $this->state->typeForName($id);
                if ($namedType !== null) {
                    return true;
                }

                $anyDefined = 0;
                foreach ($this->state->injector->inspect($id) as $definitions) {
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
                $object = $this->state->injector->make(
                    $classType,
                    $this->convertAutowireableParameterSet($parameters)
                );

                return $object;
            }

            public function backingContainer() : Injector {
                return $this->state->injector;
            }

            public function invoke(callable $callable, AutowireableParameterSet $parameters = null) : mixed {
                return $this->state->injector->execute(
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
                            assert($parameterValue instanceof ObjectType);

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
}

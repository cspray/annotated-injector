<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use DI\Container;
use DI\Definition\Reference;
use Cspray\AnnotatedContainer\Profiles;
use function DI\autowire;
use function DI\factory;
use function DI\get;

final class PhpDiContainerFactoryState implements ContainerFactoryState {

    use HasMethodInjectState, HasServicePrepareState {
        HasMethodInjectState::addMethodInject as addResolvedMethodInject;
    }

    private array $services = [];

    private array $definitions = [];

    private array $serviceKeys = [];

    public function __construct(
        private readonly ContainerDefinition $containerDefinition
    ) {
        $this->services[] = AutowireableFactory::class;
        $this->services[] = AutowireableInvoker::class;
        $this->services[] = Profiles::class;
    }

    /**
     * @param class-string $class
     * @param non-empty-string $method
     * @param non-empty-string $param
     * @return void
     */
    public function addMethodInject(string $class, string $method, string $param, mixed $value) : void {
        if ($value instanceof ContainerReference) {
            $value = get($value->name);
        }

        if ($value instanceof ServiceCollectorReference) {
            $values = [];
            foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                if ($serviceDefinition->isAbstract()) {
                    continue;
                }

                if (is_a($serviceDefinition->getType()->getName(), $value->valueType->getName(), true)) {
                    $values[] = get($serviceDefinition->getType()->getName());
                }
            }

            $value = factory(function(Container $container) use ($values, $value) {
                $resolvedValues = [];
                /** @var Reference $val */
                foreach ($values as $val) {
                    $resolvedValues[] = $val->resolve($container);
                }
                return $value->listOf->toCollection($resolvedValues);
            });
        }

        $this->addResolvedMethodInject($class, $method, $param, $value);
    }

    public function definitions() : array {
        return $this->definitions;
    }

    public function services() : array {
        return $this->services;
    }

    public function addService(string $service) : void {
        $this->services[] = $service;
    }

    public function autowireService(string $service) : void {
        $this->definitions[$service] = autowire();
    }

    public function referenceService(string $name, string $service) : void {
        $this->definitions[$name] = get($service);
    }

    public function factoryService(string $name, \Closure $closure) : void {
        $this->definitions[$name] = $closure;
    }

    public function setServiceKey(string $serviceType, string $key) : void {
        $this->serviceKeys[$serviceType] = $key;
    }

    public function serviceKey(string $serviceType) : ?string {
        return $this->serviceKeys[$serviceType] ?? null;
    }
}

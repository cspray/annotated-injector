<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Closure;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use DI\Container;
use DI\Definition\Helper\AutowireDefinitionHelper;
use DI\Definition\Helper\DefinitionHelper;
use DI\Definition\Reference;
use Cspray\AnnotatedContainer\Profiles;
use function DI\autowire;
use function DI\factory;
use function DI\get;

final class PhpDiContainerFactoryState implements ContainerFactoryState {

    use HasMethodInjectState, HasServicePrepareState {
        HasMethodInjectState::addMethodInject as addResolvedMethodInject;
    }

    /**
     * @var list<non-empty-string>
     */
    private array $services = [];

    /**
     * @var array<non-empty-string, Reference|DefinitionHelper|AutowireDefinitionHelper|Closure>
     */
    private array $definitions = [];

    /**
     * @var array<class-string, non-empty-string>
     */
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
            foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                if ($serviceDefinition->isAbstract() || $serviceDefinition->type()->name() === $class) {
                    continue;
                }

                if (is_a($serviceDefinition->type()->name(), $value->valueType->name(), true)) {
                    $values[] = get($serviceDefinition->type()->name());
                }
            }

            $value = factory(function(Container $container) use ($values, $value) : mixed {
                /** @var list<object> $resolvedValues */
                $resolvedValues = [];
                /** @var Reference $val */
                foreach ($values as $val) {
                    $resolvedVal = $val->resolve($container);
                    assert(is_object($resolvedVal));
                    $resolvedValues[] = $resolvedVal;
                }
                return $value->listOf->toCollection($resolvedValues);
            });
        }

        $this->addResolvedMethodInject($class, $method, $param, $value);
    }

    /**
     * @return array<non-empty-string, Reference|DefinitionHelper|AutowireDefinitionHelper|Closure>
     */
    public function definitions() : array {
        return $this->definitions;
    }

    /**
     * @return list<non-empty-string>
     */
    public function services() : array {
        return $this->services;
    }

    /**
     * @param non-empty-string $service
     * @return void
     */
    public function addService(string $service) : void {
        $this->services[] = $service;
    }

    /**
     * @param class-string $service
     * @return void
     */
    public function autowireService(string $service) : void {
        $this->definitions[$service] = autowire();
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $service
     * @return void
     */
    public function referenceService(string $name, string $service) : void {
        $this->definitions[$name] = get($service);
    }

    /**
     * @param non-empty-string $name
     * @param Closure $closure
     * @return void
     */
    public function factoryService(string $name, Closure $closure) : void {
        $this->definitions[$name] = $closure;
    }

    /**
     * @param class-string $serviceType
     * @param non-empty-string $key
     * @return void
     */
    public function setServiceKey(string $serviceType, string $key) : void {
        $this->serviceKeys[$serviceType] = $key;
    }

    /**
     * @param class-string $serviceType
     * @return non-empty-string|null
     */
    public function serviceKey(string $serviceType) : ?string {
        return $this->serviceKeys[$serviceType] ?? null;
    }
}

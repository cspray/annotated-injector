<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Illuminate\Contracts\Container\Container;

/**
 * @internal
 */
final class IlluminateContainerFactoryState implements ContainerFactoryState {

    use HasMethodInjectState, HasServicePrepareState;

    /**
     * @var array<class-string, array{delegateType: class-string, delegateMethod: non-empty-string, isStatic: bool}>
     */
    private array $delegates = [];

    /**
     * @var array<class-string>
     */
    private array $concreteServices = [];

    /**
     * @var array<class-string>
     */
    private array $abstractServices = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $aliases = [];

    /**
     * @var array<class-string, non-empty-string>
     */
    private array $namedServices = [];

    public function __construct(
        public readonly Container $container,
        public readonly ContainerDefinition $containerDefinition
    ) {
    }

    /**
     * @param class-string $service
     * @param class-string $delegate
     * @param non-empty-string $method
     * @return void
     */
    public function addStaticDelegate(string $service, string $delegate, string $method) : void {
        $this->delegates[$service] = [
            'delegateType' => $delegate,
            'delegateMethod' => $method,
            'isStatic' => true
        ];
    }

    public function addInstanceDelegate(string $service, string $delegate, string $method) : void {
        $this->delegates[$service] = [
            'delegateType' => $delegate,
            'delegateMethod' => $method,
            'isStatic' => false
        ];
    }

    public function addAbstractService(string $service) : void {
        $this->abstractServices[] = $service;
    }

    public function addConcreteService(string $service) : void {
        $this->concreteServices[] = $service;
    }

    public function addNamedService(string $service, string $name) : void {
        $this->namedServices[$service] = $name;
    }

    public function addAlias(string $abstract, string $concrete) : void {
        $this->aliases[$abstract] = $concrete;
    }

    public function getAbstractServices() : array {
        return $this->abstractServices;
    }

    public function getConcreteServices() : array {
        return $this->concreteServices;
    }

    public function getAliases() : array {
        return $this->aliases;
    }

    /**
     * @return array<class-string, array{delegateType: class-string, delegateMethod: non-empty-string, isStatic: bool}>
     */
    public function getDelegates() : array {
        return $this->delegates;
    }

    public function getNamedServices() : array {
        return $this->namedServices;
    }
}

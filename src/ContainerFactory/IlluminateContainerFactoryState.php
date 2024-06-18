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
     * @var list<class-string>
     */
    private array $concreteServices = [];

    /**
     * @var list<class-string>
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

    /**
     * @param class-string $service
     * @param class-string $delegate
     * @param non-empty-string $method
     * @return void
     */
    public function addInstanceDelegate(string $service, string $delegate, string $method) : void {
        $this->delegates[$service] = [
            'delegateType' => $delegate,
            'delegateMethod' => $method,
            'isStatic' => false
        ];
    }

    /**
     * @param class-string $service
     * @return void
     */
    public function addAbstractService(string $service) : void {
        $this->abstractServices[] = $service;
    }

    /**
     * @param class-string $service
     * @return void
     */
    public function addConcreteService(string $service) : void {
        $this->concreteServices[] = $service;
    }

    /**
     * @param class-string $service
     * @param non-empty-string $name
     * @return void
     */
    public function addNamedService(string $service, string $name) : void {
        $this->namedServices[$service] = $name;
    }

    /**
     * @param class-string $abstract
     * @param class-string $concrete
     * @return void
     */
    public function addAlias(string $abstract, string $concrete) : void {
        $this->aliases[$abstract] = $concrete;
    }

    /**
     * @return list<class-string>
     */
    public function abstractServices() : array {
        return $this->abstractServices;
    }

    /**
     * @return list<class-string>
     */
    public function concreteServices() : array {
        return $this->concreteServices;
    }

    /**
     * @return array<class-string, class-string>
     */
    public function aliases() : array {
        return $this->aliases;
    }

    /**
     * @return array<class-string, array{delegateType: class-string, delegateMethod: non-empty-string, isStatic: bool}>
     */
    public function delegates() : array {
        return $this->delegates;
    }

    /**
     * @return array<class-string, non-empty-string>
     */
    public function namedServices() : array {
        return $this->namedServices;
    }
}

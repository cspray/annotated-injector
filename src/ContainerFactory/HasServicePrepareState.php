<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

trait HasServicePrepareState {

    /**
     * @var array<class-string, non-empty-list<non-empty-string>>
     */
    private array $servicePrepares = [];

    /**
     * @param class-string $class
     * @param non-empty-string $method
     */
    public function addServicePrepare(string $class, string $method) : void {
        $this->servicePrepares[$class] ??= [];
        $this->servicePrepares[$class][] = $method;
    }

    /**
     * @return array<class-string, non-empty-list<non-empty-string>>
     */
    public function servicePrepares() : array {
        return $this->servicePrepares;
    }
}

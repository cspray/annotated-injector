<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

trait HasServicePrepareState {

    /**
     * @var array<class-string, list<string>>
     */
    private array $servicePrepares = [];

    public function addServicePrepare(string $class, string $method) : void {
        $this->servicePrepares[$class] ??= [];
        $this->servicePrepares[$class][] = $method;
    }

    public function servicePrepares() : array {
        return $this->servicePrepares;
    }
}

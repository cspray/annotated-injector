<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

/**
 * @internal
 */
trait HasMethodInjectState {

    /**
     * @var array<class-string, array<non-empty-string, array<non-empty-string, mixed>>>
     */
    private array $methodInject = [];

    /**
     * @param class-string $class
     * @param non-empty-string $method
     * @param non-empty-string $param
     * @return void
     */
    public function addMethodInject(string $class, string $method, string $param, mixed $value) : void {
        $this->methodInject[$class] ??= [];
        $this->methodInject[$class][$method] ??= [];
        $this->methodInject[$class][$method][$param] = $value;
    }

    /**
     * @param class-string $class
     * @param non-empty-string $method
     * @return array<non-empty-string, mixed>
     */
    public function parametersForMethod(string $class, string $method) : array {
        return $this->methodInject[$class][$method] ?? [];
    }

    /**
     * @return array<class-string, array<non-empty-string, array<non-empty-string, mixed>>>
     */
    public function methodInject() : array {
        return $this->methodInject;
    }
}

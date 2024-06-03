<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

/**
 * @internal
 */
trait HasPropertyInjectState {

    /**
     * @var array<class-string, array<non-empty-string, mixed>>
     */
    private array $propertyInject = [];

    public function addPropertyInject(string $class, string $property, mixed $value) : void {
        $this->propertyInject[$class] ??= [];
        $this->propertyInject[$class][$property] = $value;
    }

    public function propertiesToInject(string $class) : array {
        return $this->propertyInject[$class] ?? [];
    }

    /**
     * @return array<class-string, array<non-empty-string, mixed>>
     */
    public function getPropertyInject() : array {
        return $this->propertyInject;
    }
}

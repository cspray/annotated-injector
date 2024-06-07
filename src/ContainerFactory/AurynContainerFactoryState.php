<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\Injector;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\Typiphy\ObjectType;

final class AurynContainerFactoryState implements ContainerFactoryState {

    use HasMethodInjectState, HasServicePrepareState {
        HasMethodInjectState::addMethodInject as addResolvedMethodInject;
    }

    public readonly Injector $injector;

    /**
     * @var array<non-empty-string, ObjectType>
     */
    private array $nameTypeMap = [];


    public function __construct(
        private readonly ContainerDefinition $containerDefinition
    ) {
        $this->injector = new Injector();
    }


    /**
     * @param class-string $class
     * @param non-empty-string $method
     * @param non-empty-string $param
     * @return void
     * @throws \Auryn\InjectionException
     */
    public function addMethodInject(string $class, string $method, string $param, mixed $value) : void {
        if ($value instanceof ContainerReference) {
            $key = $param;
            $nameType = $this->typeForName($value->name);
            if ($nameType !== null) {
                $value = $nameType->getName();
            } else {
                $value = $value->name;
            }
        } elseif ($value instanceof ServiceCollectorReference) {
            $key = '+' . $param;
            $values = [];
            foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                if ($serviceDefinition->isAbstract() || $serviceDefinition->type()->getName() === $class) {
                    continue;
                }

                if (is_a($serviceDefinition->type()->getName(), $value->valueType->getName(), true)) {
                    $values[] = $this->injector->make($serviceDefinition->type()->getName());
                }
            }

            $value = static fn() => $value->listOf->toCollection($values);
        } else {
            $key = ':' . $param;
        }

        $this->addResolvedMethodInject($class, $method, $key, $value);
    }

    /**
     * @param non-empty-string $name
     * @param ObjectType $type
     * @return void
     */
    public function addNameType(string $name, ObjectType $type) : void {
        $this->nameTypeMap[$name] = $type;
    }

    /**
     * @param non-empty-string $name
     * @return ObjectType|null
     */
    public function typeForName(string $name) : ?ObjectType {
        return $this->nameTypeMap[$name] ?? null;
    }
}

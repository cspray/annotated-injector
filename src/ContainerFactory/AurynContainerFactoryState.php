<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Auryn\Injector;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Reflection\Type;

final class AurynContainerFactoryState implements ContainerFactoryState {

    use HasMethodInjectState, HasServicePrepareState {
        HasMethodInjectState::addMethodInject as addResolvedMethodInject;
    }

    public readonly Injector $injector;

    /**
     * @var array<non-empty-string, Type>
     */
    private array $nameTypeMap = [];


    public function __construct(
        private readonly ContainerDefinition $containerDefinition
    ) {
        $this->injector = new Injector();
    }


    /**
     * @template T
     * @param class-string<T> $class
     * @param non-empty-string $method
     * @param non-empty-string $param
     */
    public function addMethodInject(string $class, string $method, string $param, mixed $value) : void {
        if ($value instanceof ContainerReference) {
            $key = $param;
            $nameType = $this->typeForName($value->name);
            if ($nameType !== null) {
                $value = $nameType->name();
            } else {
                $value = $value->name;
            }
        } elseif ($value instanceof ServiceCollectorReference) {
            $key = '+' . $param;
            $values = [];
            foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                if ($serviceDefinition->isAbstract() || $serviceDefinition->type()->name() === $class) {
                    continue;
                }

                if (is_a($serviceDefinition->type()->name(), $value->valueType->name(), true)) {
                    /** @var T $objectValue */
                    $objectValue = $this->injector->make($serviceDefinition->type()->name());
                    $values[] = $objectValue;
                }
            }

            $value = static fn() : mixed => $value->listOf->toCollection($values);
        } else {
            $key = ':' . $param;
        }

        $this->addResolvedMethodInject($class, $method, $key, $value);
    }

    /**
     * @param non-empty-string $name
     * @param Type $type
     * @return void
     */
    public function addNameType(string $name, Type $type) : void {
        $this->nameTypeMap[$name] = $type;
    }

    /**
     * @param non-empty-string $name
     * @return Type|null
     */
    public function typeForName(string $name) : ?Type {
        return $this->nameTypeMap[$name] ?? null;
    }
}

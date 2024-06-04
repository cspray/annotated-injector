<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\Typiphy\ObjectType;

/**
 * The preferred method for constructing AliasDefinition instances.
 */
final class AliasDefinitionBuilder {

    private ObjectType $abstractType;
    private ObjectType $concreteType;

    private function __construct() {
    }

    /**
     * Define the abstract Service that should have an alias defined for it.
     *
     * @param ObjectType $serviceDefinition
     * @return static
     */
    public static function forAbstract(ObjectType $serviceDefinition) : self {
        $instance = new self;
        $instance->abstractType = $serviceDefinition;
        return $instance;
    }

    /**
     * Define the concrete Service that acts as an alias for the given abstract Service.
     *
     * This method is immutable and a new AliasDefinitionBuilder will be returned.
     *
     * @param ObjectType $serviceDefinition
     * @return $this
     */
    public function withConcrete(ObjectType $serviceDefinition) : self {
        $instance = clone $this;
        $instance->concreteType = $serviceDefinition;
        return $instance;
    }

    /**
     * Returns an AliasDefinition with the provided abstract and concrete Services.
     *
     * @return AliasDefinition
     */
    public function build() : AliasDefinition {
        return new class($this->abstractType, $this->concreteType) implements AliasDefinition {
            public function __construct(
                private readonly ObjectType $abstractService,
                private readonly ObjectType $concreteService
            ) {
            }

            public function abstractService() : ObjectType {
                return $this->abstractService;
            }

            public function concreteService() : ObjectType {
                return $this->concreteService;
            }
        };
    }
}

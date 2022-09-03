<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\Typiphy\ObjectType;

/**
 * The preferred method for creating ConfigurationDefinition.
 */
final class ConfigurationDefinitionBuilder {

    private ObjectType $classType;
    private ?string $name = null;

    private function __construct() {}

    public static function forClass(ObjectType $objectType) : self {
        $instance = new self;
        $instance->classType = $objectType;
        return $instance;
    }

    public function withName(string $name) : self {
        $instance = clone $this;
        $instance->name = $name;
        return $instance;
    }

    public function build() : ConfigurationDefinition {
        return new class($this->classType, $this->name) implements ConfigurationDefinition {
            public function __construct(
                private readonly ObjectType $classType,
                private readonly ?string $name
            ) {}

            public function getClass() : ObjectType {
                return $this->classType;
            }

            public function getName() : ?string {
                return $this->name;
            }

            public function getAttribute() : ?object {
                // TODO: Implement getAttribute() method.
            }
        };
    }

}
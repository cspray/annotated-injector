<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateConfigurationInFavorOfCustomServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ConfigurationAttribute;
use Cspray\Typiphy\ObjectType;
use JetBrains\PhpStorm\Deprecated;

/**
 * The preferred method for creating ConfigurationDefinition.
 */
#[
    DeprecateConfigurationInFavorOfCustomServiceAttribute,
    Deprecated('See ADR record DeprecatedConfigurationInFavorOfCustomServiceAttribute')
]
final class ConfigurationDefinitionBuilder {

    private ObjectType $classType;
    private ?string $name = null;
    private ?ConfigurationAttribute $attribute = null;

    private function __construct() {
    }

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

    public function withAttribute(ConfigurationAttribute $attribute) : self {
        $instance = clone $this;
        $instance->attribute = $attribute;
        return $instance;
    }

    public function build() : ConfigurationDefinition {
        return new class($this->classType, $this->name, $this->attribute) implements ConfigurationDefinition {
            public function __construct(
                private readonly ObjectType $classType,
                private readonly ?string $name,
                private readonly ?ConfigurationAttribute $attribute
            ) {
            }

            public function getClass() : ObjectType {
                return $this->classType;
            }

            public function getName() : ?string {
                return $this->name;
            }

            public function getAttribute() : ?ConfigurationAttribute {
                return $this->attribute;
            }
        };
    }
}

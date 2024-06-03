<?php

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Exception\InvalidInjectDefinition;
use Cspray\AnnotatedContainer\Internal\MethodParameterInjectTargetIdentifier;
use Cspray\AnnotatedContainer\Internal\PropertyInjectTargetIdentifier;
use Cspray\Typiphy\ObjectType;
use Cspray\Typiphy\Type;
use Cspray\Typiphy\TypeIntersect;
use Cspray\Typiphy\TypeUnion;

final class InjectDefinitionBuilder {

    private ObjectType $service;
    private ?string $method = null;
    private ?string $paramName = null;
    private ?string $property = null;
    private Type|TypeUnion|TypeIntersect $type;
    private mixed $value;
    private bool $isValueCalled = false;
    private ?InjectAttribute $attribute = null;

    /**
     * @var list<non-empty-string>
     */
    private array $profiles = [];
    private ?string $store = null;

    private function __construct() {
    }

    public static function forService(ObjectType $type) : self {
        $instance = new self();
        $instance->service = $type;
        return $instance;
    }

    public function withMethod(string $method, Type|TypeUnion|TypeIntersect $type, string $paramName) : self {
        $instance = clone $this;
        $instance->method = $method;
        $instance->paramName = $paramName;
        $instance->type = $type;
        return $instance;
    }

    public function withProperty(Type|TypeUnion|TypeIntersect $type, string $name) : self {
        $instance = clone $this;
        $instance->type = $type;
        $instance->property = $name;
        return $instance;
    }

    public function withValue(mixed $value) : self {
        $instance = clone $this;
        $instance->value = $value;
        $instance->isValueCalled = true;
        return $instance;
    }

    /**
     * @param non-empty-string $profile
     * @param non-empty-string ...$additionalProfiles
     */
    public function withProfiles(string $profile, string...$additionalProfiles) : self {
        $instance = clone $this;
        $instance->profiles[] = $profile;
        foreach ($additionalProfiles as $additionalProfile) {
            $instance->profiles[] = $additionalProfile;
        }
        return $instance;
    }

    public function withStore(string $storeName) : self {
        $instance = clone $this;
        $instance->store = $storeName;
        return $instance;
    }

    public function withAttribute(InjectAttribute $injectAttribute) : self {
        $instance = clone $this;
        $instance->attribute = $injectAttribute;
        return $instance;
    }

    public function build() : InjectDefinition {
        if (!isset($this->method) && !isset($this->property)) {
            throw InvalidInjectDefinition::fromMissingMethodAndProperty();
        } elseif (isset($this->method) && isset($this->property)) {
            throw InvalidInjectDefinition::fromMethodAndPropertySet();
        } elseif (!$this->isValueCalled) {
            throw InvalidInjectDefinition::fromMissingValue();
        }

        if (isset($this->method)) {
            assert(isset($this->paramName));
            $targetIdentifier = new MethodParameterInjectTargetIdentifier($this->paramName, $this->method, $this->service);
        } else {
            assert(isset($this->property));
            $targetIdentifier = new PropertyInjectTargetIdentifier($this->property, $this->service);
        }

        $profiles = $this->profiles;
        if (empty($profiles)) {
            $profiles[] = 'default';
        }

        return new class($targetIdentifier, $this->type, $this->value, $this->store, $profiles, $this->attribute) implements InjectDefinition {

            /**
             * @param InjectTargetIdentifier $targetIdentifier
             * @param Type|TypeUnion|TypeIntersect $type
             * @param string|null $store
             * @param list<non-empty-string> $profiles
             */
            public function __construct(
                private readonly InjectTargetIdentifier $targetIdentifier,
                private readonly Type|TypeUnion|TypeIntersect $type,
                private readonly mixed $annotationValue,
                private readonly ?string $store,
                private readonly array $profiles,
                private readonly ?InjectAttribute $attribute
            ) {
            }

            public function getTargetIdentifier() : InjectTargetIdentifier {
                return $this->targetIdentifier;
            }

            public function getType() : Type|TypeUnion|TypeIntersect {
                return $this->type;
            }

            public function getValue() : mixed {
                return $this->annotationValue;
            }

            public function getProfiles() : array {
                return $this->profiles;
            }

            public function getStoreName() : ?string {
                return $this->store;
            }

            public function getAttribute() : ?InjectAttribute {
                return $this->attribute;
            }
        };
    }
}

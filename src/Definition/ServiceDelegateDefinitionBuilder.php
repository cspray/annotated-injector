<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegateDefinition;
use Cspray\Typiphy\ObjectType;

final class ServiceDelegateDefinitionBuilder {

    private ObjectType $service;
    private ObjectType $delegateType;
    private string $delegateMethod;
    private ?ServiceDelegateAttribute $attribute = null;

    private function __construct() {
    }

    public static function forService(ObjectType $service) : self {
        $instance = new self;
        $instance->service = $service;
        return $instance;
    }

    public function withDelegateMethod(ObjectType $delegateType, string $delegateMethod) : self {
        if (trim($delegateMethod) === '') {
            throw InvalidServiceDelegateDefinition::fromEmptyDelegateMethod();
        }
        $instance = clone $this;
        $instance->delegateType = $delegateType;
        $instance->delegateMethod = $delegateMethod;
        return $instance;
    }

    public function withAttribute(ServiceDelegateAttribute $attribute) : self {
        $instance = clone $this;
        $instance->attribute = $attribute;
        return $instance;
    }

    public function build() : ServiceDelegateDefinition {
        return new class($this->service, $this->delegateType, $this->delegateMethod, $this->attribute) implements ServiceDelegateDefinition {


            public function __construct(
                private readonly ObjectType $serviceDefinition,
                private readonly ObjectType $delegateType,
                private readonly string $delegateMethod,
                private readonly ?ServiceDelegateAttribute $attribute
            ) {
            }

            public function delegateType() : ObjectType {
                return $this->delegateType;
            }

            public function delegateMethod() : string {
                return $this->delegateMethod;
            }

            public function serviceType() : ObjectType {
                return $this->serviceDefinition;
            }

            public function attribute() : ?ServiceDelegateAttribute {
                return $this->attribute;
            }
        };
    }
}

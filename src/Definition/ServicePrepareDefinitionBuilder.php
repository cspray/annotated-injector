<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepareDefinition;
use Cspray\Typiphy\ObjectType;

final class ServicePrepareDefinitionBuilder {

    private ObjectType $service;
    private string $method;
    private ?ServicePrepareAttribute $attribute = null;

    private function __construct() {
    }

    public static function forMethod(ObjectType $serviceDefinition, string $method) : self {
        if (empty($method)) {
            throw InvalidServicePrepareDefinition::fromEmptyPrepareMethod();
        }
        $instance = new self;
        $instance->service = $serviceDefinition;
        $instance->method = $method;
        return $instance;
    }

    public function withAttribute(ServicePrepareAttribute $attribute) : self {
        $instance = clone $this;
        $instance->attribute = $attribute;
        return $instance;
    }

    public function build() : ServicePrepareDefinition {
        return new class($this->service, $this->method, $this->attribute) implements ServicePrepareDefinition {

            public function __construct(
                private readonly ObjectType $service,
                private readonly string $method,
                private readonly ?ServicePrepareAttribute $attribute
            ) {
            }

            public function getService() : ObjectType {
                return $this->service;
            }

            public function getMethod() : string {
                return $this->method;
            }

            public function getAttribute() : ?ServicePrepareAttribute {
                return $this->attribute;
            }
        };
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepareDefinition;
use Cspray\Typiphy\ObjectType;

final class ServicePrepareDefinitionBuilder {

    private ObjectType $service;
    /**
     * @var non-empty-string
     */
    private string $method;
    private ?ServicePrepareAttribute $attribute = null;

    private function __construct() {
    }

    /**
     * @param ObjectType $serviceDefinition
     * @param non-empty-string $method
     * @return self
     * @throws InvalidServicePrepareDefinition
     */
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

            /**
             * @param ObjectType $service
             * @param non-empty-string $method
             * @param ServicePrepareAttribute|null $attribute
             */
            public function __construct(
                private readonly ObjectType $service,
                private readonly string $method,
                private readonly ?ServicePrepareAttribute $attribute
            ) {
            }

            public function service() : ObjectType {
                return $this->service;
            }

            /**
             * @return non-empty-string
             */
            public function methodName() : string {
                return $this->method;
            }

            public function attribute() : ?ServicePrepareAttribute {
                return $this->attribute;
            }
        };
    }
}

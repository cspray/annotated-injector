<?php

namespace Cspray\AnnotatedContainer;

class ServicePrepareDefinitionBuilder {

    private ServiceDefinition $service;
    private string $method;

    private function __construct() {}

    public static function forMethod(ServiceDefinition $serviceDefinition, string $method) : self {
        if (empty($method)) {
            throw new \InvalidArgumentException('A method for a ServicePrepareDefinition must not be blank.');
        }
        $instance = new self;
        $instance->service = $serviceDefinition;
        $instance->method = $method;
        return $instance;
    }

    public function build() : ServicePrepareDefinition {
        return new class($this->service, $this->method) implements ServicePrepareDefinition {

            private ServiceDefinition $service;
            private string $method;

            public function __construct(ServiceDefinition $service, string $method) {
                $this->service = $service;
                $this->method = $method;
            }

            public function getService(): ServiceDefinition {
                return $this->service;
            }

            public function getMethod(): string {
                return $this->method;
            }
        };
    }

}
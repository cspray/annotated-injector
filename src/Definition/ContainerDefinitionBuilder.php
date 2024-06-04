<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

/**
 * The preferred method for constructing ContainerDefinition instances.
 */
final class ContainerDefinitionBuilder {

    /**
     * @var list<ServiceDefinition>
     */
    private array $serviceDefinitions = [];

    /**
     * @var list<AliasDefinition>
     */
    private array $aliasDefinitions = [];

    /**
     * @var list<ServicePrepareDefinition>
     */
    private array $servicePrepareDefinitions = [];

    /**
     * @var list<InjectDefinition>
     */
    private array $injectDefinitions = [];

    /**
     * @var list<ServiceDelegateDefinition>
     */
    private array $serviceDelegateDefinitions = [];


    private function __construct() {
    }

    /**
     * @return static
     */
    public static function newDefinition() : self {
        return new self;
    }

    public function withServiceDefinition(ServiceDefinition $serviceDefinition) : self {
        $instance = clone $this;
        $instance->serviceDefinitions[] = $serviceDefinition;
        return $instance;
    }

    public function withAliasDefinition(AliasDefinition $aliasDefinition) : self {
        $instance = clone $this;
        $instance->aliasDefinitions[] = $aliasDefinition;
        return $instance;
    }

    public function withServicePrepareDefinition(ServicePrepareDefinition $servicePrepareDefinition) : self {
        $instance = clone $this;
        $instance->servicePrepareDefinitions[] = $servicePrepareDefinition;
        return $instance;
    }

    public function withServiceDelegateDefinition(ServiceDelegateDefinition $serviceDelegateDefinition) : self {
        $instance = clone $this;
        $instance->serviceDelegateDefinitions[] = $serviceDelegateDefinition;
        return $instance;
    }

    public function withInjectDefinition(InjectDefinition $injectDefinition) : self {
        $instance = clone $this;
        $instance->injectDefinitions[] = $injectDefinition;
        return $instance;
    }

    /**
     * @return list<ServiceDefinition>
     */
    public function getServiceDefinitions() : array {
        return $this->serviceDefinitions;
    }

    public function build() : ContainerDefinition {
        return new class(
            $this->serviceDefinitions,
            $this->aliasDefinitions,
            $this->servicePrepareDefinitions,
            $this->injectDefinitions,
            $this->serviceDelegateDefinitions,
        ) implements ContainerDefinition {

            /**
             * @param list<ServiceDefinition> $serviceDefinitions
             * @param list<AliasDefinition> $aliasDefinitions
             * @param list<ServicePrepareDefinition> $servicePrepareDefinitions
             * @param list<InjectDefinition> $injectDefinitions
             * @param list<ServiceDelegateDefinition> $serviceDelegateDefinitions
             */
            public function __construct(
                private readonly array $serviceDefinitions,
                private readonly array $aliasDefinitions,
                private readonly array $servicePrepareDefinitions,
                private readonly array $injectDefinitions,
                private readonly array $serviceDelegateDefinitions,
            ) {
            }

            public function serviceDefinitions(): array {
                return $this->serviceDefinitions;
            }

            public function aliasDefinitions(): array {
                return $this->aliasDefinitions;
            }

            public function servicePrepareDefinitions(): array {
                return $this->servicePrepareDefinitions;
            }

            public function injectDefinitions(): array {
                return $this->injectDefinitions;
            }

            public function serviceDelegateDefinitions(): array {
                return $this->serviceDelegateDefinitions;
            }
        };
    }
}

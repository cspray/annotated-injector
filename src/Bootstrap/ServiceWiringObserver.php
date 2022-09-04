<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;

abstract class ServiceWiringObserver implements Observer {

    final public function beforeCompilation() : void {
        // noop
    }

    final public function afterCompilation(ContainerDefinition $containerDefinition) : void {
        // noop
    }

    final public function beforeContainerCreation(ContainerDefinition $containerDefinition) : void {
        // noop
    }

    final public function afterContainerCreation(ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $serviceGatherer = new class($containerDefinition, $container) implements ServiceGatherer {

            public function __construct(
                private readonly ContainerDefinition $containerDefinition,
                private readonly AnnotatedContainer $container
            ) {}

            public function getServicesForType(string $type) : array {
                /** @var array<array-key, object> $services */
                $services = [];
                foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                    if ($serviceDefinition->isAbstract()) {
                        continue;
                    }

                    /** @var class-string $serviceType */
                    $serviceType = $serviceDefinition->getType()->getName();
                    if (is_a($serviceType, $type, true)) {
                        $service = $this->container->get($serviceType);
                        assert($service instanceof $type);
                        $services[] = new class($service, $serviceDefinition) implements ServiceFromServiceDefinition {

                            public function __construct(
                                private readonly object $service,
                                private readonly ServiceDefinition $definition
                            ) {}

                            public function getService() : object {
                                return $this->service;
                            }

                            public function getDefinition() : ServiceDefinition {
                                return $this->definition;
                            }
                        };
                    }
                }

                return $services;
            }
        };
        $this->wireServices($container, $serviceGatherer);
    }

    abstract protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void;

}
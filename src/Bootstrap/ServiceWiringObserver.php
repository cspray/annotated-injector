<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\DeprecateObserversInFavorOfEventSystem;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use JetBrains\PhpStorm\Deprecated;

/**
 * @deprecated
 */
#[
    DeprecateObserversInFavorOfEventSystem,
    Deprecated('Please see DeprecateObserversInFavorOfEventSystem ADR')
]
abstract class ServiceWiringObserver implements ContainerCreatedObserver {

    final public function notifyContainerCreated(ActiveProfiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $serviceGatherer = new class($containerDefinition, $container) implements ServiceGatherer {

            private readonly ContainerDefinition $containerDefinition;

            public function __construct(
                ContainerDefinition $containerDefinition,
                private readonly AnnotatedContainer $container
            ) {
                $activeProfiles = $container->get(ActiveProfiles::class);
                assert($activeProfiles instanceof ActiveProfiles);
                $this->containerDefinition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles->getProfiles());
            }

            public function getServicesForType(string $type) : array {
                /** @var array<array-key, object> $services */
                $services = [];
                foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                    if ($serviceDefinition->isAbstract()) {
                        continue;
                    }

                    $serviceType = $serviceDefinition->getType()->getName();
                    if (is_a($serviceType, $type, true)) {
                        $service = $this->container->get($serviceType);
                        assert($service instanceof $type);
                        $services[] = $this->createServiceFromServiceDefinition($service, $serviceDefinition);
                    }
                }

                return $services;
            }

            public function getServicesWithAttribute(string $attributeType) : array {
                $services = [];
                foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
                    if ($serviceDefinition->isAbstract()) {
                        continue;
                    }

                    $serviceAttribute = $serviceDefinition->getAttribute();
                    if (!($serviceAttribute instanceof $attributeType)) {
                        continue;
                    }

                    $service = $this->container->get($serviceDefinition->getType()->getName());
                    assert(is_object($service));
                    $services[] = $this->createServiceFromServiceDefinition($service, $serviceDefinition);
                }
                return $services;
            }

            private function createServiceFromServiceDefinition(object $service, ServiceDefinition $serviceDefinition) : ServiceFromServiceDefinition {
                return new class($service, $serviceDefinition) implements ServiceFromServiceDefinition {
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
        };
        $this->wireServices($container, $serviceGatherer);
    }

    abstract protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void;

}
<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\AfterContainerCreation;
use Cspray\AnnotatedContainer\Profiles;

abstract class ServiceWiringListener implements AfterContainerCreation {

    abstract protected function wireServices(AnnotatedContainer $container, ServiceGatherer $gatherer) : void;

    public function handleAfterContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $serviceGatherer = new class($containerDefinition, $container) implements ServiceGatherer {

            private readonly ContainerDefinition $containerDefinition;

            public function __construct(
                ContainerDefinition $containerDefinition,
                private readonly AnnotatedContainer $container
            ) {
                $activeProfiles = $container->get(Profiles::class);
                assert($activeProfiles instanceof Profiles);
                $this->containerDefinition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles);
            }

            /**
             * @param class-string $type
             * @return list<ServiceFromServiceDefinition>
             */
            public function getServicesForType(string $type) : array {
                /** @var list<ServiceFromServiceDefinition> $services */
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
                    ) {
                    }

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
}

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

            public function servicesForType(string $type) : array {
                /** @var list<ServiceFromServiceDefinition> $services */
                $services = [];
                foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                    if ($serviceDefinition->isAbstract()) {
                        continue;
                    }

                    $serviceType = $serviceDefinition->type()->getName();
                    if (is_a($serviceType, $type, true)) {
                        $service = $this->container->get($serviceType);
                        assert($service instanceof $type);
                        $services[] = $this->createServiceFromServiceDefinition($service, $serviceDefinition);
                    }
                }

                return $services;
            }

            public function servicesWithAttribute(string $attributeType) : array {
                $services = [];
                foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                    if ($serviceDefinition->isAbstract()) {
                        continue;
                    }

                    $serviceAttribute = $serviceDefinition->attribute();
                    if (!($serviceAttribute instanceof $attributeType)) {
                        continue;
                    }

                    $service = $this->container->get($serviceDefinition->type()->getName());
                    assert(is_object($service));
                    $services[] = $this->createServiceFromServiceDefinition($service, $serviceDefinition);
                }
                return $services;
            }

            /**
             * @template T of object
             * @param T $service
             * @param ServiceDefinition $serviceDefinition
             * @return ServiceFromServiceDefinition<T>
             */
            private function createServiceFromServiceDefinition(object $service, ServiceDefinition $serviceDefinition) : ServiceFromServiceDefinition {
                return new class($service, $serviceDefinition) implements ServiceFromServiceDefinition {
                    public function __construct(
                        private readonly object $service,
                        private readonly ServiceDefinition $definition
                    ) {
                    }

                    public function service() : object {
                        return $this->service;
                    }

                    public function definition() : ServiceDefinition {
                        return $this->definition;
                    }
                };
            }
        };
        $this->wireServices($container, $serviceGatherer);
    }
}

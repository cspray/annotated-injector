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

    final public function handleAfterContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void {
        $serviceGatherer = new class($containerDefinition, $container) implements ServiceGatherer {

            private readonly ContainerDefinition $containerDefinition;

            public function __construct(
                ContainerDefinition $containerDefinition,
                private readonly AnnotatedContainer $container
            ) {
                $activeProfiles = $container->get(Profiles::class);
                $this->containerDefinition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles);
            }

            /**
             * @template T of object
             * @param class-string<T> $type
             * @return list<ServiceFromServiceDefinition<T>>
             */
            public function servicesForType(string $type) : array {
                /** @var list<ServiceFromServiceDefinition<T>> $services */
                $services = [];
                foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
                    if ($serviceDefinition->isAbstract()) {
                        continue;
                    }

                    $serviceType = $serviceDefinition->type()->name();
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

                    $service = $this->container->get($serviceDefinition->type()->name());
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
                $serviceFromDefinition =

                    /**
                     * @implements ServiceFromServiceDefinition<T>
                     */
                    new class($service, $serviceDefinition) implements ServiceFromServiceDefinition {
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

                /** @var ServiceFromServiceDefinition<T> $serviceFromDefinition */
                return $serviceFromDefinition;
            }
        };
        $this->wireServices($container, $serviceGatherer);
    }
}

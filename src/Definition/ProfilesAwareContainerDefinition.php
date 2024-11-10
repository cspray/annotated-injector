<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\Reflection\Type;

final class ProfilesAwareContainerDefinition implements ContainerDefinition {

    public function __construct(
        private readonly ContainerDefinition $containerDefinition,
        private readonly Profiles $activeProfiles
    ) {
    }

    public function serviceDefinitions() : array {
        return array_values(array_filter(
            $this->containerDefinition->serviceDefinitions(),
            fn(ServiceDefinition $definition) => $this->hasActiveProfile($definition)
        ));
    }

    public function aliasDefinitions() : array {
        $filtered = [];
        foreach ($this->containerDefinition->aliasDefinitions() as $aliasDefinition) {
            $abstract = $this->getServiceDefinition($aliasDefinition->abstractService());
            if ($abstract === null) {
                throw InvalidAlias::fromAbstractNotService($aliasDefinition->abstractService()->name());
            }

            $concrete = $this->getServiceDefinition($aliasDefinition->concreteService());
            if ($concrete === null) {
                throw InvalidAlias::fromConcreteNotService($aliasDefinition->concreteService()->name());
            }

            if ($this->hasActiveProfile($abstract) && $this->hasActiveProfile($concrete)) {
                $filtered[] = $aliasDefinition;
            }
        }
        return $filtered;
    }

    public function servicePrepareDefinitions() : array {
        return $this->containerDefinition->servicePrepareDefinitions();
    }

    public function serviceDelegateDefinitions() : array {
        return array_values(array_filter(
            $this->containerDefinition->serviceDelegateDefinitions(),
            fn(ServiceDelegateDefinition $definition) => $this->hasActiveProfile($definition)
        ));
    }

    public function injectDefinitions() : array {
        return array_values(array_filter(
            $this->containerDefinition->injectDefinitions(),
            fn(InjectDefinition $definition) => $this->hasActiveProfile($definition)
        ));
    }

    private function getServiceDefinition(Type $objectType) : ?ServiceDefinition {
        foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->type() === $objectType) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    private function hasActiveProfile(ServiceDefinition|InjectDefinition|ServiceDelegateDefinition $definition) : bool {
        return $this->activeProfiles->isAnyActive($definition->profiles());
    }
}

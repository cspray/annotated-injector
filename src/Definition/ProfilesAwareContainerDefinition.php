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
        $filtered = [];
        foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
            if ($this->hasActiveProfile($serviceDefinition)) {
                $filtered[] = $serviceDefinition;
            }
        }

        return $filtered;
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
        return $this->containerDefinition->serviceDelegateDefinitions();
    }

    public function injectDefinitions() : array {
        $filtered = [];
        foreach ($this->containerDefinition->injectDefinitions() as $injectDefinition) {
            if ($this->hasActiveProfile($injectDefinition)) {
                $filtered[] = $injectDefinition;
            }
        }
        return $filtered;
    }

    private function getServiceDefinition(Type $objectType) : ?ServiceDefinition {
        foreach ($this->containerDefinition->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->type() === $objectType) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    private function hasActiveProfile(ServiceDefinition|InjectDefinition $definition) : bool {
        return $this->activeProfiles->isAnyActive($definition->profiles());
    }
}

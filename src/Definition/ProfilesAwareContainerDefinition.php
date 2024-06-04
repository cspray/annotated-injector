<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\Typiphy\ObjectType;

final class ProfilesAwareContainerDefinition implements ContainerDefinition {

    public function __construct(
        private readonly ContainerDefinition $containerDefinition,
        private readonly Profiles $activeProfiles
    ) {
    }

    public function getServiceDefinitions() : array {
        $filtered = [];
        foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($this->hasActiveProfile($serviceDefinition)) {
                $filtered[] = $serviceDefinition;
            }
        }

        return $filtered;
    }

    public function getAliasDefinitions() : array {
        $filtered = [];
        foreach ($this->containerDefinition->getAliasDefinitions() as $aliasDefinition) {
            $abstract = $this->getServiceDefinition($aliasDefinition->abstractService());
            if ($abstract === null) {
                throw InvalidAlias::fromAbstractNotService($aliasDefinition->abstractService()->getName());
            }

            $concrete = $this->getServiceDefinition($aliasDefinition->concreteService());
            if ($concrete === null) {
                throw InvalidAlias::fromConcreteNotService($aliasDefinition->concreteService()->getName());
            }

            if ($this->hasActiveProfile($abstract) && $this->hasActiveProfile($concrete)) {
                $filtered[] = $aliasDefinition;
            }
        }
        return $filtered;
    }

    public function getServicePrepareDefinitions() : array {
        return $this->containerDefinition->getServicePrepareDefinitions();
    }

    public function getServiceDelegateDefinitions() : array {
        return $this->containerDefinition->getServiceDelegateDefinitions();
    }

    public function getInjectDefinitions() : array {
        $filtered = [];
        foreach ($this->containerDefinition->getInjectDefinitions() as $injectDefinition) {
            if ($this->hasActiveProfile($injectDefinition)) {
                $filtered[] = $injectDefinition;
            }
        }
        return $filtered;
    }

    private function getServiceDefinition(ObjectType $objectType) : ?ServiceDefinition {
        foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    private function hasActiveProfile(ServiceDefinition|InjectDefinition $definition) : bool {
        return $this->activeProfiles->isAnyActive($definition->getProfiles());
    }
}

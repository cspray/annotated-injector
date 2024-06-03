<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

use Cspray\AnnotatedContainer\Exception\InvalidAlias;
use Cspray\Typiphy\ObjectType;

final class ProfilesAwareContainerDefinition implements ContainerDefinition {

    public function __construct(
        private readonly ContainerDefinition $containerDefinition,
        private readonly array $activeProfiles
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
            $abstract = $this->getServiceDefinition($aliasDefinition->getAbstractService());
            if ($abstract === null) {
                throw InvalidAlias::fromAbstractNotService($aliasDefinition->getAbstractService()->getName());
            }

            $concrete = $this->getServiceDefinition($aliasDefinition->getConcreteService()) ?? $this->getConfigurationDefinition($aliasDefinition->getConcreteService());
            if ($concrete === null) {
                throw InvalidAlias::fromConcreteNotService($aliasDefinition->getConcreteService()->getName());
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

    public function getConfigurationDefinitions() : array {
        return $this->containerDefinition->getConfigurationDefinitions();
    }

    private function getServiceDefinition(ObjectType $objectType) : ?ServiceDefinition {
        foreach ($this->containerDefinition->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    private function hasActiveProfile(ServiceDefinition|InjectDefinition|ConfigurationDefinition $definition) : bool {
        if ($definition instanceof ConfigurationDefinition) {
            return true;
        }

        return count(array_intersect($this->activeProfiles, $definition->getProfiles())) >= 1;
    }

    private function getConfigurationDefinition(ObjectType $objectType) : ?ConfigurationDefinition {
        foreach ($this->getConfigurationDefinitions() as $configurationDefinition) {
            if ($configurationDefinition->getClass() === $objectType) {
                return $configurationDefinition;
            }
        }

        return null;
    }
}

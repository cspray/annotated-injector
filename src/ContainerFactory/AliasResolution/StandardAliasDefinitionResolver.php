<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\AliasResolution;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\Typiphy\ObjectType;

final class StandardAliasDefinitionResolver implements AliasDefinitionResolver {

    public function resolveAlias(
        ContainerDefinition $containerDefinition,
        ObjectType $abstractService
    ) : AliasDefinitionResolution {
        if ($this->isServiceDelegate($containerDefinition, $abstractService)) {
            $definition = null;
            $reason = AliasResolutionReason::ServiceIsDelegated;
        } else {
            $aliases = [];
            foreach ($containerDefinition->aliasDefinitions() as $aliasDefinition) {
                if ($aliasDefinition->abstractService()->getName() === $abstractService->getName()) {
                    $aliases[] = $aliasDefinition;
                }
            }

            if (count($aliases) === 1) {
                $definition = $aliases[0];
                $reason = AliasResolutionReason::SingleConcreteService;
            } elseif (count($aliases) > 1) {
                $definition = null;
                $primaryAliases = [];
                foreach ($aliases as $alias) {
                    $concreteDefinition = $this->serviceDefinition($containerDefinition, $alias->concreteService());
                    if ($concreteDefinition?->isPrimary()) {
                        $primaryAliases[] = $alias;
                    }
                }

                if (count($primaryAliases) === 1) {
                    $definition = $primaryAliases[0];
                    $reason = AliasResolutionReason::ConcreteServiceIsPrimary;
                } elseif (count($primaryAliases) === 0) {
                    $reason = AliasResolutionReason::MultipleConcreteService;
                } else {
                    $reason = AliasResolutionReason::MultiplePrimaryService;
                }
            } else {
                $definition = null;
                $reason = AliasResolutionReason::NoConcreteService;
            }
        }

        return new class($reason, $definition) implements AliasDefinitionResolution {

            public function __construct(
                private readonly AliasResolutionReason $reason,
                private readonly ?AliasDefinition $definition
            ) {
            }

            public function aliasResolutionReason() : AliasResolutionReason {
                return $this->reason;
            }

            public function aliasDefinition() : ?AliasDefinition {
                return $this->definition;
            }
        };
    }

    private function serviceDefinition(ContainerDefinition $containerDefinition, ObjectType $objectType) : ?ServiceDefinition {
        foreach ($containerDefinition->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType()->getName() === $objectType->getName()) {
                return $serviceDefinition;
            }
        }

        return null;
    }

    private function isServiceDelegate(ContainerDefinition $containerDefinition, ObjectType $service) : bool {
        foreach ($containerDefinition->serviceDelegateDefinitions() as $serviceDelegateDefinition) {
            if ($serviceDelegateDefinition->getServiceType()->getName() === $service->getName()) {
                return true;
            }
        }

        return false;
    }
}

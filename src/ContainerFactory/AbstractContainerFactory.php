<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\Typiphy\ObjectType;
use UnitEnum;

abstract class AbstractContainerFactory implements ContainerFactory {

    protected readonly AliasDefinitionResolver $aliasDefinitionResolver;


    /**
     * @var ParameterStore[]
     */
    private array $parameterStores = [];

    public function __construct(
        AliasDefinitionResolver $aliasDefinitionResolver = null
    ) {
        // Injecting environment variables is something we have supported since early versions.
        // We don't require adding this parameter store explicitly to continue providing this functionality
        // without the end-user having to change how they construct their ContainerFactory.
        $this->addParameterStore(new EnvironmentParameterStore());
        $this->aliasDefinitionResolver = $aliasDefinitionResolver ?? new StandardAliasDefinitionResolver();
    }

    final public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $activeProfiles = $containerFactoryOptions?->getProfiles() ?? Profiles::fromList(['default']);

        $state = $this->createContainerState($containerDefinition, $activeProfiles);

        $container = $this->createAnnotatedContainer($state, $activeProfiles);

        return $container;
    }

    private function createContainerState(ContainerDefinition $containerDefinition, Profiles $activeProfiles) : ContainerFactoryState {
        $definition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles);
        $state = $this->getContainerFactoryState($definition);

        foreach ($definition->getInjectDefinitions() as $injectDefinition) {
            $this->handleInjectDefinition($state, $injectDefinition);
        }

        foreach ($definition->getServiceDefinitions() as $serviceDefinition) {
            $this->handleServiceDefinition($state, $serviceDefinition);
        }

        foreach ($definition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $this->handleServiceDelegateDefinition($state, $serviceDelegateDefinition);
        }

        foreach ($definition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            $this->handleServicePrepareDefinition($state, $servicePrepareDefinition);
        }

        foreach ($definition->getAliasDefinitions() as $aliasDefinition) {
            $resolution = $this->aliasDefinitionResolver->resolveAlias($definition, $aliasDefinition->getAbstractService());
            $this->handleAliasDefinition($state, $resolution);
        }

        return $state;
    }

    /**
     * Add a custom ParameterStore, allowing you to Inject arbitrary values into your Services.
     *
     * @param ParameterStore $parameterStore
     * @return void
     * @see Inject
     */
    final public function addParameterStore(ParameterStore $parameterStore): void {
        $this->parameterStores[$parameterStore->getName()] = $parameterStore;
    }

    final protected function getParameterStore(string $storeName) : ?ParameterStore {
        return $this->parameterStores[$storeName] ?? null;
    }

    final protected function getInjectDefinitionValue(InjectDefinition $definition) : mixed {
        $value = $definition->getValue();
        $store = $definition->getStoreName();
        if ($store !== null) {
            $parameterStore = $this->getParameterStore($store);
            if ($parameterStore === null) {
                throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($store);
            }
            $value = $parameterStore->fetch($definition->getType(), $value);
        }

        $type = $definition->getType();
        if ($value instanceof ListOf) {
            $value = new ServiceCollectorReference(
                $value,
                $value->type(),
                $type
            );
        } elseif ($type instanceof ObjectType && !is_a($definition->getType()->getName(), UnitEnum::class, true)) {
            $value = new ContainerReference($value, $type);
        }

        return $value;
    }


    abstract protected function getBackingContainerType() : ObjectType;

    abstract protected function getContainerFactoryState(ContainerDefinition $containerDefinition) : ContainerFactoryState;

    abstract protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void;

    abstract protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void;

    abstract protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void;

    abstract protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void;

    abstract protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void;

    abstract protected function createAnnotatedContainer(ContainerFactoryState $state, Profiles $activeProfiles) : AnnotatedContainer;
}

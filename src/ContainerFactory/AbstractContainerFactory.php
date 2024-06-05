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
use Cspray\AnnotatedContainer\Event\ContainerFactoryEmitter;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\Typiphy\ObjectType;
use UnitEnum;

abstract class AbstractContainerFactory implements ContainerFactory {

    private readonly ?ContainerFactoryEmitter $emitter;

    /**
     * @var ParameterStore[]
     */
    private array $parameterStores = [];

    public function __construct(
        ContainerFactoryEmitter $emitter,
        private readonly AliasDefinitionResolver $aliasDefinitionResolver = new StandardAliasDefinitionResolver(),
    ) {
        // Injecting environment variables is something we have supported since early versions.
        // We don't require adding this parameter store explicitly to continue providing this functionality
        // without the end-user having to change how they construct their ContainerFactory.
        $this->addParameterStore(new EnvironmentParameterStore());
        $this->emitter = $emitter;
    }

    final public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $activeProfiles = $containerFactoryOptions?->profiles() ?? Profiles::fromList(['default']);

        $this->emitter?->emitBeforeContainerCreation($activeProfiles, $containerDefinition);

        $container = $this->createAnnotatedContainer(
            $this->createContainerState($containerDefinition, $activeProfiles),
            $activeProfiles
        );

        $this->emitter?->emitAfterContainerCreation($activeProfiles, $containerDefinition, $container);

        return $container;
    }

    private function createContainerState(ContainerDefinition $containerDefinition, Profiles $activeProfiles) : ContainerFactoryState {
        $definition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles);
        $state = $this->containerFactoryState($definition);

        foreach ($definition->serviceDefinitions() as $serviceDefinition) {
            $this->handleServiceDefinition($state, $serviceDefinition);
        }

        foreach ($definition->serviceDelegateDefinitions() as $serviceDelegateDefinition) {
            $this->handleServiceDelegateDefinition($state, $serviceDelegateDefinition);
        }

        foreach ($definition->servicePrepareDefinitions() as $servicePrepareDefinition) {
            $this->handleServicePrepareDefinition($state, $servicePrepareDefinition);
        }

        foreach ($definition->aliasDefinitions() as $aliasDefinition) {
            $resolution = $this->aliasDefinitionResolver->resolveAlias($definition, $aliasDefinition->abstractService());
            $this->handleAliasDefinition($state, $resolution);
        }

        foreach ($definition->injectDefinitions() as $injectDefinition) {
            $this->handleInjectDefinition($state, $injectDefinition);
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
        $this->parameterStores[$parameterStore->name()] = $parameterStore;
    }

    final protected function parameterStore(string $storeName) : ?ParameterStore {
        return $this->parameterStores[$storeName] ?? null;
    }

    final protected function injectDefinitionValue(InjectDefinition $definition) : mixed {
        $value = $definition->value();
        $store = $definition->storeName();
        if ($store !== null) {
            $parameterStore = $this->parameterStore($store);
            if ($parameterStore === null) {
                throw ParameterStoreNotFound::fromParameterStoreNotAddedToContainerFactory($store);
            }
            $value = $parameterStore->fetch($definition->type(), $value);
        }

        $type = $definition->type();
        if ($value instanceof ListOf) {
            $value = new ServiceCollectorReference(
                $value,
                $value->type(),
                $type
            );
        } elseif ($type instanceof ObjectType && !is_a($definition->type()->getName(), UnitEnum::class, true)) {
            $value = new ContainerReference($value, $type);
        }

        return $value;
    }


    abstract protected function backingContainerType() : ObjectType;

    abstract protected function containerFactoryState(ContainerDefinition $containerDefinition) : ContainerFactoryState;

    abstract protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void;

    abstract protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void;

    abstract protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void;

    abstract protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void;

    abstract protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void;

    abstract protected function createAnnotatedContainer(ContainerFactoryState $state, Profiles $activeProfiles) : AnnotatedContainer;
}

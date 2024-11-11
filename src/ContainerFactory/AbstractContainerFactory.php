<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\State\ContainerFactoryState;
use Cspray\AnnotatedContainer\ContainerFactory\State\InjectParameterValue;
use Cspray\AnnotatedContainer\ContainerFactory\State\ServiceCollectorReference;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\ContainerFactoryEmitter;
use Cspray\AnnotatedContainer\Profiles;

/**
 * @template ContainerBuilder of object
 * @template IntermediaryContainer of object
 */
abstract class AbstractContainerFactory implements ContainerFactory {

    private readonly ?ContainerFactoryEmitter $emitter;

    /**
     * @var array<non-empty-string, ParameterStore>
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
        $activeProfiles = $containerFactoryOptions?->profiles() ?? Profiles::defaultOnly();

        $this->emitter?->emitBeforeContainerCreation($activeProfiles, $containerDefinition);

        $state = new ContainerFactoryState(
            new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles),
            $activeProfiles,
            $this->aliasDefinitionResolver,
            $this->parameterStores,
        );
        $container = $this->createAnnotatedContainer($state);

        $this->emitter?->emitAfterContainerCreation($activeProfiles, $containerDefinition, $container);

        return $container;
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @return array<non-empty-string, mixed>
     */
    final protected function parametersForServiceConstructorToArray(
        object                $containerBuilder,
        ContainerFactoryState $state,
        ServiceDefinition     $serviceDefinition
    ) : array {
        return $this->listOfInjectDefinitionsToArray(
            $containerBuilder,
            $state,
            $state->constructorInjectDefinitionsForServiceDefinition($serviceDefinition)
        );
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @return array<non-empty-string, mixed>
     */
    final protected function parametersForServicePrepareToArray(
        object                   $containerBuilder,
        ContainerFactoryState    $state,
        ServicePrepareDefinition $definition,
    ) : array {
        return $this->listOfInjectDefinitionsToArray(
            $containerBuilder,
            $state,
            $state->injectDefinitionsForServicePrepareDefinition($definition)
        );
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @return array<non-empty-string, mixed>
     */
    final protected function parametersForServiceDelegateToArray(
        object                    $containerBuilder,
        ContainerFactoryState     $state,
        ServiceDelegateDefinition $definition,
    ) : array {
        return $this->listOfInjectDefinitionsToArray(
            $containerBuilder,
            $state,
            $state->injectDefinitionsForServiceDelegateDefinition($definition)
        );
    }

    /**
     * @param IntermediaryContainer $container
     * @param ServiceCollectorReference $reference
     * @return list<object>|object
     */
    final protected function serviceCollectorReferenceToListOfServices(
        object $container,
        ContainerFactoryState $state,
        InjectDefinition $definition,
        ServiceCollectorReference $reference
    ) : array|object {
        $values = [];
        foreach ($state->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->isAbstract() ||
                $serviceDefinition->type()->equals($definition->service()) ||
                !is_a($serviceDefinition->type()->name(), $reference->valueType->name(), true)
            ) {
                continue;
            }

            $values[] = $this->retrieveServiceFromIntermediaryContainer($container, $serviceDefinition);
        }

        return $reference->listOf->toCollection($values);
    }

    /**
     * @param ContainerBuilder $containerBuilder
     * @param list<InjectDefinition> $definitions
     * @return array<non-empty-string, mixed>
     */
    private function listOfInjectDefinitionsToArray(object $containerBuilder, ContainerFactoryState $state, array $definitions) : array {
        $params = [];
        foreach ($definitions as $injectDefinition) {
            $injectParameterValue = $this->resolveParameterForInjectDefinition($containerBuilder, $state, $injectDefinition);
            $params[$injectParameterValue->name] = $injectParameterValue->value;
        }

        return $params;
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

    abstract protected function createAnnotatedContainer(ContainerFactoryState $state) : AnnotatedContainer;

    /**
     * @param ContainerBuilder $containerBuilder
     */
    abstract protected function resolveParameterForInjectDefinition(
        object                $containerBuilder,
        ContainerFactoryState $state,
        InjectDefinition      $definition,
    ) : InjectParameterValue;

    /**
     * @param IntermediaryContainer $container
     */
    abstract protected function retrieveServiceFromIntermediaryContainer(
        object $container,
        ServiceDefinition $definition
    ) : object;
}

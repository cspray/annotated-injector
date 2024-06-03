<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory;

use Brick\VarExporter\VarExporter;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolution;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasDefinitionResolver;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\StandardAliasDefinitionResolver;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ProfilesAwareContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Exception\ContainerException;
use Cspray\AnnotatedContainer\Exception\ParameterStoreNotFound;
use Cspray\AnnotatedContainer\Profiles\ActiveProfiles;
use Cspray\AnnotatedContainer\Profiles\ActiveProfilesBuilder;
use Cspray\Typiphy\ObjectType;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use stdClass;
use Throwable;
use UnitEnum;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\objectType;

abstract class AbstractContainerFactory implements ContainerFactory {

    protected readonly AliasDefinitionResolver $aliasDefinitionResolver;

    private LoggerInterface $logger;

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
        $this->logger = new NullLogger();
    }

    final public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        $this->setLoggerFromOptions($containerFactoryOptions);
        $activeProfiles = $containerFactoryOptions?->getActiveProfiles() ?? ['default'];
        $containerType = $this->getBackingContainerType();

        $this->logCreatingContainer($containerType, $activeProfiles);
        $this->logServicesNotMatchingProfiles(
            $containerDefinition,
            $activeProfiles
        );

        $state = $this->createContainerState($containerDefinition, $activeProfiles);

        $container = $this->createAnnotatedContainer($state, $this->createActiveProfilesService($activeProfiles));

        $this->logFinishedCreatingContainer($containerType, $activeProfiles);

        return $container;
    }

    private function createContainerState(ContainerDefinition $containerDefinition, array $activeProfiles) : ContainerFactoryState {
        $definition = new ProfilesAwareContainerDefinition($containerDefinition, $activeProfiles);
        $state = $this->getContainerFactoryState($definition);

        foreach ($definition->getServiceDefinitions() as $serviceDefinition) {
            $this->handleServiceDefinition($state, $serviceDefinition);
            $this->logServiceShared($serviceDefinition);
            if ($serviceDefinition->getName() !== null) {
                $this->logServiceNamed($serviceDefinition);
            }
        }

        foreach ($definition->getServiceDelegateDefinitions() as $serviceDelegateDefinition) {
            $this->handleServiceDelegateDefinition($state, $serviceDelegateDefinition);
            $this->logServiceDelegate($serviceDelegateDefinition);
        }

        foreach ($definition->getServicePrepareDefinitions() as $servicePrepareDefinition) {
            $this->handleServicePrepareDefinition($state, $servicePrepareDefinition);
            $this->logServicePrepare($servicePrepareDefinition);
        }

        foreach ($definition->getConfigurationDefinitions() as $configurationDefinition) {
            $this->handleConfigurationDefinition($state, $configurationDefinition);
            $this->logConfigurationShared($configurationDefinition);
            if ($configurationDefinition->getName() !== null) {
                $this->logConfigurationNamed($configurationDefinition);
            }
        }

        foreach ($definition->getAliasDefinitions() as $aliasDefinition) {
            $resolution = $this->aliasDefinitionResolver->resolveAlias($definition, $aliasDefinition->getAbstractService());
            $this->handleAliasDefinition($state, $resolution);
            $this->logAliasingService($resolution, $aliasDefinition->getAbstractService());
        }

        // We're doing inject definitions first because these could influence the way a service is created
        foreach ($definition->getInjectDefinitions() as $injectDefinition) {
            $this->handleInjectDefinition($state, $injectDefinition);
            if ($injectDefinition->getTargetIdentifier()->isMethodParameter()) {
                $this->logInjectingMethodParameter($injectDefinition);
            } else {
                $this->logInjectingProperty($injectDefinition);
            }
        }

        return $state;
    }

    /**
     * @param list<non-empty-string> $profiles
     * @return ActiveProfiles
     */
    private function createActiveProfilesService(array $profiles) : ActiveProfiles {
        return new class($profiles) implements ActiveProfiles {
            public function __construct(
                /** @var list<non-empty-string> */
                private readonly array $profiles
            ) {
            }

            public function getProfiles() : array {
                return $this->profiles;
            }

            public function isActive(string $profile) : bool {
                return in_array($profile, $this->profiles, true);
            }
        };
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

    /**
     * @deprecated
     */
    final protected function setLoggerFromOptions(?ContainerFactoryOptions $options) : void {
        $this->logger = $options?->getLogger() ?? new NullLogger();
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

    /**
     * @deprecated
     */
    final protected function logCreatingContainer(ObjectType $backingImplementation, array $activeProfiles) : void {
        $this->logger->info(
            sprintf(
                'Started wiring AnnotatedContainer with %s backing implementation and "%s" active profiles.',
                $backingImplementation->getName(),
                implode(', ', $activeProfiles)
            ),
            [
                'backingImplementation' => $backingImplementation->getName(),
                'activeProfiles' => $activeProfiles
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logFinishedCreatingContainer(ObjectType $backingImplementation, array $activeProfiles) : void {
        $this->logger->info(
            'Finished wiring AnnotatedContainer.',
            [
                'backingImplementation' => $backingImplementation->getName(),
                'activeProfiles' => $activeProfiles
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logServiceShared(ServiceDefinition $service) : void {
        $this->logger->info(
            sprintf('Shared service %s.', $service->getType()->getName()),
            [
                'service' => $service->getType()->getName()
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logConfigurationShared(ConfigurationDefinition $configuration) : void {
        $this->logger->info(
            sprintf('Shared configuration %s.', $configuration->getClass()->getName()),
            [
                'configuration' => $configuration->getClass()->getName()
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logServiceNamed(ServiceDefinition $service) : void {
        $this->logger->info(
            sprintf('Aliased name "%s" to service %s.', (string) $service->getName(), $service->getType()->getName()),
            [
                'service' => $service->getType()->getName(),
                'name' => $service->getName()
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logConfigurationNamed(ConfigurationDefinition $configuration) : void {
        $this->logger->info(
            sprintf('Aliased name "%s" to configuration %s.', (string) $configuration->getName(), $configuration->getClass()->getName()),
            [
                'configuration' => $configuration->getClass()->getName(),
                'name' => $configuration->getName()
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logServiceDelegate(ServiceDelegateDefinition $delegate) : void {
        $this->logger->info(
            sprintf(
                'Delegated construction of service %s to %s::%s.',
                $delegate->getServiceType()->getName(),
                $delegate->getDelegateType()->getName(),
                $delegate->getDelegateMethod()
            ),
            [
                'service' => $delegate->getServiceType()->getName(),
                'delegatedType' => $delegate->getDelegateType()->getName(),
                'delegatedMethod' => $delegate->getDelegateMethod()
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logServicePrepare(ServicePrepareDefinition $prepare) : void {
        $this->logger->info(
            sprintf(
                'Preparing service %s with method %s.',
                $prepare->getService()->getName(),
                $prepare->getMethod()
            ),
            [
                'service' => $prepare->getService()->getName(),
                'method' => $prepare->getMethod()
            ]
        );
    }

    /**
     * @deprecated
     */
    final protected function logAliasingService(AliasDefinitionResolution $resolution, ObjectType $abstractService) : void {
        $aliasDefinition = $resolution->getAliasDefinition();
        if ($aliasDefinition !== null) {
            $this->logger->info(
                sprintf(
                    'Alias resolution attempted for abstract service %s. Found concrete service %s, because %s.',
                    $aliasDefinition->getAbstractService()->getName(),
                    $aliasDefinition->getConcreteService()->getName(),
                    $resolution->getAliasResolutionReason()->name
                ),
                [
                    'abstractService' => $aliasDefinition->getAbstractService()->getName(),
                    'concreteService' => $aliasDefinition->getConcreteService()->getName(),
                    'aliasingReason' => $resolution->getAliasResolutionReason()
                ]
            );
        } else {
            $this->logger->info(
                sprintf(
                    'Alias resolution attempted for abstract service %s. No concrete service found, because %s.',
                    $abstractService->getName(),
                    $resolution->getAliasResolutionReason()->name
                ),
                [
                    'abstractService' => $abstractService->getName(),
                    'concreteService' => null,
                    'aliasingReason' => $resolution->getAliasResolutionReason()
                ]
            );
        }
    }

    /**
     * @deprecated
     */
    final protected function logInjectingMethodParameter(InjectDefinition $inject) : void {
        $storeName = $inject->getStoreName();
        $methodName = $inject->getTargetIdentifier()->getMethodName();
        assert($methodName !== null);
        if ($storeName === null) {
            if ($inject->getType() instanceof ObjectType && is_a($inject->getValue(), UnitEnum::class, true)) {
                $this->logger->info(
                    sprintf(
                        'Injecting enum "%s" into %s::%s($%s).',
                        VarExporter::export($inject->getValue(), VarExporter::INLINE_ARRAY),
                        $inject->getTargetIdentifier()->getClass()->getName(),
                        $methodName,
                        $inject->getTargetIdentifier()->getName()
                    ),
                    [
                        'service' => $inject->getTargetIdentifier()->getClass()->getName(),
                        'method' => $methodName,
                        'parameter' => $inject->getTargetIdentifier()->getName(),
                        'type' => $inject->getType()->getName(),
                        'value' => $inject->getValue()
                    ]
                );
            } elseif ($inject->getType() instanceof ObjectType && !($inject->getValue() instanceof ListOf)) {
                $this->logger->info(
                    sprintf(
                        'Injecting service %s from Container into %s::%s($%s).',
                        $inject->getValue(),
                        $inject->getTargetIdentifier()->getClass()->getName(),
                        $methodName,
                        $inject->getTargetIdentifier()->getName()
                    ),
                    [
                        'service' => $inject->getTargetIdentifier()->getClass()->getName(),
                        'method' => $methodName,
                        'parameter' => $inject->getTargetIdentifier()->getName(),
                        'type' => $inject->getType()->getName(),
                        'value' => $inject->getValue()
                    ]
                );
            } else {
                $this->logger->info(
                    sprintf(
                        'Injecting value "%s" into %s::%s($%s).',
                        VarExporter::export($inject->getValue(), VarExporter::INLINE_ARRAY),
                        $inject->getTargetIdentifier()->getClass()->getName(),
                        $methodName,
                        $inject->getTargetIdentifier()->getName()
                    ),
                    [
                        'service' => $inject->getTargetIdentifier()->getClass()->getName(),
                        'method' => $methodName,
                        'parameter' => $inject->getTargetIdentifier()->getName(),
                        'type' => $inject->getType()->getName(),
                        'value' => $inject->getValue()
                    ]
                );
            }
        } else {
            $this->logger->info(
                sprintf(
                    'Injecting value from %s ParameterStore for key "%s" into %s::%s($%s).',
                    $storeName,
                    $inject->getValue(),
                    $inject->getTargetIdentifier()->getClass()->getName(),
                    $methodName,
                    $inject->getTargetIdentifier()->getName()
                ),
                [
                    'service' => $inject->getTargetIdentifier()->getClass()->getName(),
                    'method' => $methodName,
                    'parameter' => $inject->getTargetIdentifier()->getName(),
                    'type' => $inject->getType()->getName(),
                    'value' => $inject->getValue(),
                    'store' => $storeName
                ]
            );
        }
    }

    /**
     * @deprecated
     */
    final protected function logInjectingProperty(InjectDefinition $inject) : void {
        $storeName = $inject->getStoreName();
        if ($storeName === null) {
            if ($inject->getType() instanceof ObjectType && is_a($inject->getValue(), UnitEnum::class, true)) {
                $this->logger->info(
                    sprintf(
                        'Injecting enum "%s" into %s::%s.',
                        VarExporter::export($inject->getValue()),
                        $inject->getTargetIdentifier()->getClass()->getName(),
                        $inject->getTargetIdentifier()->getName()
                    ),
                    [
                        'configuration' => $inject->getTargetIdentifier()->getClass()->getName(),
                        'property' => $inject->getTargetIdentifier()->getName(),
                        'type' => $inject->getType()->getName(),
                        'value' => $inject->getValue()
                    ]
                );
            } elseif ($inject->getType() instanceof ObjectType) {
                $this->logger->info(
                    sprintf(
                        'Injecting service %s from Container into %s::%s.',
                        $inject->getValue(),
                        $inject->getTargetIdentifier()->getClass()->getName(),
                        $inject->getTargetIdentifier()->getName()
                    ),
                    [
                        'configuration' => $inject->getTargetIdentifier()->getClass()->getName(),
                        'property' => $inject->getTargetIdentifier()->getName(),
                        'type' => $inject->getType()->getName(),
                        'value' => $inject->getValue()
                    ]
                );
            } else {
                $this->logger->info(
                    sprintf(
                        'Injecting value "%s" into %s::%s.',
                        VarExporter::export($inject->getValue(), VarExporter::INLINE_ARRAY),
                        $inject->getTargetIdentifier()->getClass()->getName(),
                        $inject->getTargetIdentifier()->getName()
                    ),
                    [
                        'configuration' => $inject->getTargetIdentifier()->getClass()->getName(),
                        'property' => $inject->getTargetIdentifier()->getName(),
                        'type' => $inject->getType()->getName(),
                        'value' => $inject->getValue()
                    ]
                );
            }
        } else {
            $this->logger->info(
                sprintf(
                    'Injecting value from %s ParameterStore for key "%s" into %s::%s.',
                    $storeName,
                    $inject->getValue(),
                    $inject->getTargetIdentifier()->getClass()->getName(),
                    $inject->getTargetIdentifier()->getName()
                ),
                [
                    'configuration' => $inject->getTargetIdentifier()->getClass()->getName(),
                    'property' => $inject->getTargetIdentifier()->getName(),
                    'type' => $inject->getType()->getName(),
                    'value' => $inject->getValue(),
                    'store' => $storeName
                ]
            );
        }
    }

    /**
     * @param ContainerDefinition $definition
     * @param list<string> $profiles
     * @return void
     * @deprecated
     */
    final protected function logServicesNotMatchingProfiles(
        ContainerDefinition $definition,
        array $profiles
    ) {
        foreach ($definition->getServiceDefinitions() as $serviceDefinition) {
            if (count(array_intersect($profiles, $serviceDefinition->getProfiles())) >= 1) {
                continue;
            }

            $this->logger->info(
                sprintf(
                    'Not considering %s as shared service because profiles do not match.',
                    $serviceDefinition->getType()->getName()
                ),
                [
                    'service' => $serviceDefinition->getType()->getName(),
                    'profiles' => $serviceDefinition->getProfiles()
                ]
            );
        }
    }

    abstract protected function getBackingContainerType() : ObjectType;

    abstract protected function getContainerFactoryState(ContainerDefinition $containerDefinition) : ContainerFactoryState;

    abstract protected function handleServiceDefinition(ContainerFactoryState $state, ServiceDefinition $definition) : void;

    abstract protected function handleAliasDefinition(ContainerFactoryState $state, AliasDefinitionResolution $resolution) : void;

    abstract protected function handleServiceDelegateDefinition(ContainerFactoryState $state, ServiceDelegateDefinition $definition) : void;

    abstract protected function handleServicePrepareDefinition(ContainerFactoryState $state, ServicePrepareDefinition $definition) : void;

    abstract protected function handleInjectDefinition(ContainerFactoryState $state, InjectDefinition $definition) : void;

    abstract protected function handleConfigurationDefinition(ContainerFactoryState $state, ConfigurationDefinition $definition) : void;

    abstract protected function createAnnotatedContainer(ContainerFactoryState $state, ActiveProfiles $activeProfiles) : AnnotatedContainer;
}

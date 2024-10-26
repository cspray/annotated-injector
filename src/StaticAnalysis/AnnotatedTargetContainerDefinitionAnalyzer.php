<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Attribute\InjectAttribute;
use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServiceAttribute;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegateAttribute;
use Cspray\AnnotatedContainer\Attribute\ServicePrepareAttribute;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\StaticAnalysisEmitter;
use Cspray\AnnotatedContainer\Exception\InvalidScanDirectories;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegate;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepare;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedTarget\AnnotatedTargetParser;
use Cspray\AnnotatedTarget\AnnotatedTargetParserOptions;
use Cspray\AnnotatedTarget\Exception\InvalidArgumentException;
use stdClass;
use function Cspray\AnnotatedContainer\Definition\definitionFactory;

/**
 * A ContainerDefinitionCompiler that utilizes the AnnotatedTarget concept by parsing given source code directories and
 * converting any found targets into the appropriate definition object.
 *
 * @psalm-type DefinitionsCollection = array{
 *     serviceDefinitions: list<ServiceDefinition>,
 *     servicePrepareDefinitions: list<ServicePrepareDefinition>,
 *     serviceDelegateDefinitions: list<ServiceDelegateDefinition>,
 *     injectDefinitions: list<InjectDefinition>,
 * }
 */
final class AnnotatedTargetContainerDefinitionAnalyzer implements ContainerDefinitionAnalyzer {

    public function __construct(
        private readonly AnnotatedTargetParser $annotatedTargetCompiler,
        private readonly StaticAnalysisEmitter $emitter
    ) {
    }

    /**
     * Will parse source code, according to the passed $containerDefinitionCompileOptions, and construct a ContainerDefinition
     * instance based off of the resultant parsing.
     *
     * @param ContainerDefinitionAnalysisOptions $containerDefinitionAnalysisOptions
     * @return ContainerDefinition
     * @throws InvalidArgumentException
     * @throws InvalidScanDirectories
     * @throws InvalidServiceDelegate
     * @throws InvalidServicePrepare
     */
    public function analyze(ContainerDefinitionAnalysisOptions $containerDefinitionAnalysisOptions) : ContainerDefinition {
        $scanDirs = $containerDefinitionAnalysisOptions->scanDirectories();
        if (empty($scanDirs)) {
            throw InvalidScanDirectories::fromEmptyList();
        }

        if (count(array_unique($scanDirs)) !== count($scanDirs)) {
            throw InvalidScanDirectories::fromDuplicatedDirectories();
        }

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();

        $this->emitter->emitBeforeContainerAnalysis($containerDefinitionAnalysisOptions);

        $consumer = $this->parse($containerDefinitionAnalysisOptions);
        // We need to add services from the DefinitionProvider first to ensure that any services required
        // to be defined, e.g. to satisfy a ServiceDelegate, are added to the container definition
        $containerDefinitionBuilder = $this->addThirdPartyServices(
            $containerDefinitionAnalysisOptions,
            $containerDefinitionBuilder,
        );
        $containerDefinitionBuilder = $this->addAnnotatedDefinitions($containerDefinitionBuilder, $consumer);
        $containerDefinitionBuilder = $this->addAliasDefinitions($containerDefinitionBuilder);

        $containerDefinition = $containerDefinitionBuilder->build();

        $this->emitter->emitAfterContainerAnalysis($containerDefinitionAnalysisOptions, $containerDefinition);

        return $containerDefinition;
    }

    /**
     * @param ContainerDefinitionAnalysisOptions $containerDefinitionAnalysisOptions
     * @return DefinitionsCollection
     * @throws InvalidArgumentException
     */
    private function parse(
        ContainerDefinitionAnalysisOptions $containerDefinitionAnalysisOptions,
    ) : array {
        $consumer = new stdClass();
        $consumer->serviceDefinitions = [];
        $consumer->servicePrepareDefinitions = [];
        $consumer->serviceDelegateDefinitions = [];
        $consumer->injectDefinitions = [];
        $dirs = $containerDefinitionAnalysisOptions->scanDirectories();
        $options = AnnotatedTargetParserOptions::scanForSpecificAttributes(
            $dirs,
            array_map(static fn(AttributeType $attributeType) => $attributeType->value, AttributeType::cases()),
        );

        foreach ($this->annotatedTargetCompiler->parse($options) as $target) {
            $attribute = $target->attributeInstance();
            if ($attribute instanceof ServiceAttribute) {
                $definition = definitionFactory()->serviceDefinitionFromAnnotatedTarget($target);
                $consumer->serviceDefinitions[] = $definition;
                $this->emitter->emitAnalyzedServiceDefinitionFromAttribute($target, $definition);
            } elseif ($attribute instanceof ServicePrepareAttribute) {
                $definition = definitionFactory()->servicePrepareDefinitionFromAnnotatedTarget($target);
                $consumer->servicePrepareDefinitions[] = $definition;
                $this->emitter->emitAnalyzedServicePrepareDefinitionFromAttribute($target, $definition);
            } elseif ($attribute instanceof ServiceDelegateAttribute) {
                $definition = definitionFactory()->serviceDelegateDefinitionFromAnnotatedTarget($target);
                $consumer->serviceDelegateDefinitions[] = $definition;
                $this->emitter->emitAnalyzedServiceDelegateDefinitionFromAttribute($target, $definition);
            } elseif ($attribute instanceof InjectAttribute) {
                $definition = definitionFactory()->injectDefinitionFromAnnotatedTarget($target);
                $consumer->injectDefinitions[] = $definition;
                $this->emitter->emitAnalyzedInjectDefinitionFromAttribute($target, $definition);
            }
        }

        /**
         * @var DefinitionsCollection $consumer
         */
        $consumer = (array) $consumer;
        return $consumer;
    }

    /**
     * @param ContainerDefinitionBuilder $containerDefinitionBuilder
     * @param DefinitionsCollection $consumer
     * @return ContainerDefinitionBuilder
     * @throws InvalidServiceDelegate
     * @throws InvalidServicePrepare
     */
    private function addAnnotatedDefinitions(
        ContainerDefinitionBuilder $containerDefinitionBuilder,
        array $consumer,
    ) : ContainerDefinitionBuilder {
        foreach ($consumer['serviceDefinitions'] as $serviceDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($serviceDefinition);
        }

        foreach ($consumer['serviceDelegateDefinitions'] as $serviceDelegateDefinition) {
            $serviceDef = $this->serviceDefinition($containerDefinitionBuilder, $serviceDelegateDefinition->serviceType());

            // We need to handle the scenario where a user is using Annotated Container with limited or no Attributes
            // In that use case the user is providing many ServiceDelegate attributes, we should not require manually
            // defining those services, but we still want them to get added to the ContainerDefinition in the end
            // to properly represent the state of the Container for tooling and analysis.
            if ($serviceDef === null) {
                $impliedThroughDelegationServiceDefinition = definitionFactory()->serviceDefinitionFromObjectTypeAndAttribute(
                    $serviceDelegateDefinition->serviceType(),
                    new Service()
                );
                $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition(
                    $impliedThroughDelegationServiceDefinition
                );
            }
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition($serviceDelegateDefinition);
        }

        $concretePrepareDefinitions = array_filter($consumer['servicePrepareDefinitions'], function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder) {
            $serviceDef = $this->serviceDefinition($containerDefinitionBuilder, $prepareDef->service());
            if (is_null($serviceDef)) {
                $exception = InvalidServicePrepare::fromClassNotService($prepareDef->service()->name(), $prepareDef->methodName());
                throw $exception;
            }
            return $serviceDef->isConcrete();
        });
        $abstractPrepareDefinitions = array_filter($consumer['servicePrepareDefinitions'], function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder) {
            $serviceDef = $this->serviceDefinition($containerDefinitionBuilder, $prepareDef->service());
            return $serviceDef?->isAbstract() ?? false;
        });

        foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition($abstractPrepareDefinition);
        }

        foreach ($concretePrepareDefinitions as $concretePrepareDefinition) {
            $hasAbstractPrepare = false;
            foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
                $concreteServiceName = $concretePrepareDefinition->service()->name();
                $abstractServiceName = $abstractPrepareDefinition->service()->name();
                if (is_subclass_of($concreteServiceName, $abstractServiceName)) {
                    $hasAbstractPrepare = true;
                    break;
                }
            }
            if (!$hasAbstractPrepare) {
                $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition($concretePrepareDefinition);
            }
        }

        foreach ($consumer['injectDefinitions'] as $injectDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withInjectDefinition($injectDefinition);
        }

        return $containerDefinitionBuilder;
    }

    private function serviceDefinition(ContainerDefinitionBuilder $containerDefinitionBuilder, Type $objectType) : ?ServiceDefinition {
        $return = null;
        foreach ($containerDefinitionBuilder->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->type() === $objectType) {
                $return = $serviceDefinition;
                break;
            }
        }
        return $return;
    }

    private function addThirdPartyServices(
        ContainerDefinitionAnalysisOptions $compileOptions,
        ContainerDefinitionBuilder         $builder,
    ) : ContainerDefinitionBuilder {
        $definitionProvider = $compileOptions->definitionProvider();
        if ($definitionProvider !== null) {
            $context = new class($builder, $this->emitter) implements DefinitionProviderContext {
                public function __construct(
                    private ContainerDefinitionBuilder $builder,
                    private StaticAnalysisEmitter $analysisEmitter
                ) {
                }

                public function getBuilder() : ContainerDefinitionBuilder {
                    return $this->builder;
                }

                public function addServiceDefinition(ServiceDefinition $serviceDefinition) : void {
                    $this->builder = $this->builder->withServiceDefinition($serviceDefinition);
                    $this->analysisEmitter->emitAddedServiceDefinitionFromApi($serviceDefinition);
                }

                public function addServicePrepareDefinition(ServicePrepareDefinition $servicePrepareDefinition) : void {
                    $this->builder = $this->builder->withServicePrepareDefinition($servicePrepareDefinition);
                    $this->analysisEmitter->emitAddedServicePrepareDefinitionFromApi($servicePrepareDefinition);
                }

                public function addServiceDelegateDefinition(ServiceDelegateDefinition $serviceDelegateDefinition) : void {
                    $this->builder = $this->builder->withServiceDelegateDefinition($serviceDelegateDefinition);
                    $this->analysisEmitter->emitAddedServiceDelegateDefinitionFromApi($serviceDelegateDefinition);
                }

                public function addInjectDefinition(InjectDefinition $injectDefinition) : void {
                    $this->builder = $this->builder->withInjectDefinition($injectDefinition);
                    $this->analysisEmitter->emitAddedInjectDefinitionFromApi($injectDefinition);
                }
            };
            $definitionProvider->consume($context);
            return $context->getBuilder();
        } else {
            return $builder;
        }
    }

    private function addAliasDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder) : ContainerDefinitionBuilder {
        /** @var list<Type> $abstractTypes */
        /** @var list<Type> $concreteTypes */
        $abstractTypes = [];
        $concreteTypes = [];

        foreach ($containerDefinitionBuilder->serviceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->isAbstract()) {
                $abstractTypes[] = $serviceDefinition->type();
            } else {
                $concreteTypes[] = $serviceDefinition->type();
            }
        }

        foreach ($abstractTypes as $abstractType) {
            foreach ($concreteTypes as $concreteType) {
                $abstractTypeString = $abstractType->name();
                if (is_subclass_of($concreteType->name(), $abstractTypeString)) {
                    $aliasDefinition = definitionFactory()->aliasDefinition($abstractType, $concreteType);
                    $containerDefinitionBuilder = $containerDefinitionBuilder->withAliasDefinition($aliasDefinition);
                    $this->emitter->emitAddedAliasDefinition($aliasDefinition);
                }
            }
        }

        return $containerDefinitionBuilder;
    }
}

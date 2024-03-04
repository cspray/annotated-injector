<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\AliasDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\StaticAnalysisEmitter;
use Cspray\AnnotatedContainer\Exception\InvalidScanDirectories;
use Cspray\AnnotatedContainer\Exception\InvalidServiceDelegate;
use Cspray\AnnotatedContainer\Exception\InvalidServicePrepare;
use Cspray\AnnotatedContainer\Internal\AttributeType;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\AnnotatedTarget\AnnotatedTargetParser;
use Cspray\AnnotatedTarget\AnnotatedTargetParserOptionsBuilder;
use Cspray\AnnotatedTarget\Exception\InvalidArgumentException;
use Cspray\Typiphy\ObjectType;
use ReflectionClass;
use stdClass;
use function Cspray\Typiphy\objectType;

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
        private readonly AnnotatedTargetDefinitionConverter $definitionConverter,
        private readonly ?StaticAnalysisEmitter $emitter = null
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
        $scanDirs = $containerDefinitionAnalysisOptions->getScanDirectories();
        if (empty($scanDirs)) {
            throw InvalidScanDirectories::fromEmptyList();
        }

        if (count(array_unique($scanDirs)) !== count($scanDirs)) {
            throw InvalidScanDirectories::fromDuplicatedDirectories();
        }

        $containerDefinitionBuilder = ContainerDefinitionBuilder::newDefinition();

        $this->emitter?->emitBeforeContainerAnalysis($containerDefinitionAnalysisOptions);

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

        $this->emitter?->emitAfterContainerAnalysis($containerDefinitionAnalysisOptions, $containerDefinition);

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
        $attributeTypes = array_map(
            static fn(AttributeType $attributeType) => objectType($attributeType->value), AttributeType::cases()
        );
        $dirs = $containerDefinitionAnalysisOptions->getScanDirectories();
        $options = AnnotatedTargetParserOptionsBuilder::scanDirectories(...$dirs)
            ->filterAttributes(...$attributeTypes)
            ->build();

        /** @var AnnotatedTarget $target */
        foreach ($this->annotatedTargetCompiler->parse($options) as $target) {
            $definition = $this->definitionConverter->convert($target);

            if ($definition instanceof ServiceDefinition) {
                $consumer->serviceDefinitions[] = $definition;
                $this->emitter?->emitAnalyzedServiceDefinitionFromAttribute($target, $definition);
            } else if ($definition instanceof ServicePrepareDefinition) {
                $consumer->servicePrepareDefinitions[] = $definition;
                $this->emitter?->emitAnalyzedServicePrepareDefinitionFromAttribute($target, $definition);
            } else if ($definition instanceof ServiceDelegateDefinition) {
                $consumer->serviceDelegateDefinitions[] = $definition;
                $this->emitter?->emitAnalyzedServiceDelegateDefinitionFromAttribute($target, $definition);
            } else if ($definition instanceof InjectDefinition) {
                $consumer->injectDefinitions[] = $definition;
                $this->emitter?->emitAnalyzedInjectDefinitionFromAttribute($target, $definition);
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
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $serviceDelegateDefinition->getServiceType());
            if ($serviceDef === null) {
                $reflection = new ReflectionClass($serviceDelegateDefinition->getServiceType()->getName());
                if ($reflection->isInterface() || $reflection->isAbstract()) {
                    $serviceDef = ServiceDefinitionBuilder::forAbstract($serviceDelegateDefinition->getServiceType())->build();
                } else {
                    $serviceDef = ServiceDefinitionBuilder::forConcrete($serviceDelegateDefinition->getServiceType())->build();
                }
                $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDefinition($serviceDef);
            }
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServiceDelegateDefinition($serviceDelegateDefinition);
        }

        $concretePrepareDefinitions = array_filter($consumer['servicePrepareDefinitions'], function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            if (is_null($serviceDef)) {
                $exception = InvalidServicePrepare::fromClassNotService($prepareDef->getService()->getName(), $prepareDef->getMethod());
                throw $exception;
            }
            return $serviceDef->isConcrete();
        });
        $abstractPrepareDefinitions = array_filter($consumer['servicePrepareDefinitions'], function (ServicePrepareDefinition $prepareDef) use ($containerDefinitionBuilder) {
            $serviceDef = $this->getServiceDefinition($containerDefinitionBuilder, $prepareDef->getService());
            return $serviceDef?->isAbstract() ?? false;
        });

        foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
            $containerDefinitionBuilder = $containerDefinitionBuilder->withServicePrepareDefinition($abstractPrepareDefinition);
        }

        foreach ($concretePrepareDefinitions as $concretePrepareDefinition) {
            $hasAbstractPrepare = false;
            foreach ($abstractPrepareDefinitions as $abstractPrepareDefinition) {
                $concreteServiceName = $concretePrepareDefinition->getService()->getName();
                $abstractServiceName = $abstractPrepareDefinition->getService()->getName();
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

    private function getServiceDefinition(ContainerDefinitionBuilder $containerDefinitionBuilder, ObjectType $objectType) : ?ServiceDefinition {
        $return = null;
        foreach ($containerDefinitionBuilder->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->getType() === $objectType) {
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
        $definitionProvider = $compileOptions->getDefinitionProvider();
        if ($definitionProvider !== null) {
            $context = new class($builder) implements DefinitionProviderContext {
                public function __construct(private ContainerDefinitionBuilder $builder) {
                }

                public function getBuilder() : ContainerDefinitionBuilder {
                    return $this->builder;
                }

                public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder) : void {
                    $this->builder = $containerDefinitionBuilder;
                }
            };
            $definitionProvider->consume($context);
            return $context->getBuilder();
        } else {
            return $builder;
        }
    }

    private function addAliasDefinitions(ContainerDefinitionBuilder $containerDefinitionBuilder) : ContainerDefinitionBuilder {
        /** @var list<ObjectType> $abstractTypes */
        /** @var list<ObjectType> $concreteTypes */
        $abstractTypes = [];
        $concreteTypes = [];

        foreach ($containerDefinitionBuilder->getServiceDefinitions() as $serviceDefinition) {
            if ($serviceDefinition->isAbstract()) {
                $abstractTypes[] = $serviceDefinition->getType();
            } else {
                $concreteTypes[] = $serviceDefinition->getType();
            }
        }

        foreach ($abstractTypes as $abstractType) {
            foreach ($concreteTypes as $concreteType) {
                $abstractTypeString = $abstractType->getName();
                if (is_subclass_of($concreteType->getName(), $abstractTypeString)) {
                    $aliasDefinition = AliasDefinitionBuilder::forAbstract($abstractType)
                        ->withConcrete($concreteType)
                        ->build();
                    $containerDefinitionBuilder = $containerDefinitionBuilder->withAliasDefinition($aliasDefinition);
                    $this->emitter?->emitAddedAliasDefinition($aliasDefinition);
                }
            }
        }

        return $containerDefinitionBuilder;
    }
}

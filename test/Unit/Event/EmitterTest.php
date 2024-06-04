<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Event;

use Closure;
use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Bootstrap\BootstrappingConfiguration;
use Cspray\AnnotatedContainer\Bootstrap\ContainerAnalytics;
use Cspray\AnnotatedContainer\ContainerFactory\AliasResolution\AliasResolutionReason;
use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\Emitter;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\AfterBootstrap;
use Cspray\AnnotatedContainer\Event\Listener\Bootstrap\BeforeBootstrap;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\AfterContainerCreation;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\BeforeContainerCreation;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\InjectingMethodParameter;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\InjectingProperty;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceAliasResolution;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceDelegated;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceFilteredDueToProfiles;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServicePrepared;
use Cspray\AnnotatedContainer\Event\Listener\ContainerFactory\ServiceShared;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedAliasDefinition;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedInjectDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedServiceDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedServiceDelegateDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AddedServicePrepareDefinitionFromApi;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AfterContainerAnalysis;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedContainerDefinitionFromCache;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedInjectDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServiceDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServiceDelegateDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\AnalyzedServicePrepareDefinitionFromAttribute;
use Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis\BeforeContainerAnalysis;
use Cspray\AnnotatedContainer\Exception\InvalidListener;
use Cspray\AnnotatedContainer\Profiles;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedTarget\AnnotatedTarget;
use Cspray\PrecisionStopwatch\Duration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmitterTest extends TestCase {

    private Emitter $subject;

    protected function setUp() : void {
        $this->subject = new Emitter();
    }

    public static function listenerData() : array {
        return [
            BeforeBootstrap::class => [
                BeforeBootstrap::class,
                fn() => [
                    $this->getMockBuilder(BootstrappingConfiguration::class)->getMock()
                ],
                fn(BootstrappingConfiguration $bootstrappingConfiguration) => $this->subject->emitBeforeBootstrap($bootstrappingConfiguration),
            ],
            BeforeContainerAnalysis::class => [
                BeforeContainerAnalysis::class,
                fn() => [
                    $this->getMockBuilder(ContainerDefinitionAnalysisOptions::class)->getMock(),
                ],
                fn(ContainerDefinitionAnalysisOptions $analysisOptions) => $this->subject->emitBeforeContainerAnalysis($analysisOptions)
            ],
            AnalyzedServiceDefinitionFromAttribute::class => [
                AnalyzedServiceDefinitionFromAttribute::class,
                fn() => [
                    $this->getMockBuilder(AnnotatedTarget::class)->getMock(),
                    $this->getMockBuilder(ServiceDefinition::class)->getMock()
                ],
                fn(AnnotatedTarget $target, ServiceDefinition $serviceDefinition) =>
                    $this->subject->emitAnalyzedServiceDefinitionFromAttribute($target, $serviceDefinition)
            ],
            AnalyzedServicePrepareDefinitionFromAttribute::class => [
                AnalyzedServicePrepareDefinitionFromAttribute::class,
                fn() => [
                    $this->getMockBuilder(AnnotatedTarget::class)->getMock(),
                    $this->getMockBuilder(ServicePrepareDefinition::class)->getMock(),
                ],
                fn(AnnotatedTarget $target, ServicePrepareDefinition $servicePrepareDefinition) =>
                    $this->subject->emitAnalyzedServicePrepareDefinitionFromAttribute($target, $servicePrepareDefinition)
            ],
            AnalyzedServiceDelegateDefinitionFromAttribute::class => [
                AnalyzedServiceDelegateDefinitionFromAttribute::class,
                fn() => [
                    $this->getMockBuilder(AnnotatedTarget::class)->getMock(),
                    $this->getMockBuilder(ServiceDelegateDefinition::class)->getMock(),
                ],
                fn(AnnotatedTarget $target, ServiceDelegateDefinition $serviceDelegateDefinition) =>
                    $this->subject->emitAnalyzedServiceDelegateDefinitionFromAttribute($target, $serviceDelegateDefinition)
            ],
            AnalyzedInjectDefinitionFromAttribute::class => [
                AnalyzedInjectDefinitionFromAttribute::class,
                fn() => [
                    $this->getMockBuilder(AnnotatedTarget::class)->getMock(),
                    $this->getMockBuilder(InjectDefinition::class)->getMock(),
                ],
                fn(AnnotatedTarget $target, InjectDefinition $injectDefinition) =>
                    $this->subject->emitAnalyzedInjectDefinitionFromAttribute($target, $injectDefinition)
            ],
            AddedAliasDefinition::class => [
                AddedAliasDefinition::class,
                fn() => [
                    $this->getMockBuilder(AliasDefinition::class)->getMock(),
                ],
                fn(AliasDefinition $aliasDefinition) =>
                    $this->subject->emitAddedAliasDefinition($aliasDefinition)
            ],
            AddedInjectDefinitionFromApi::class => [
                AddedInjectDefinitionFromApi::class,
                fn() => [
                    $this->getMockBuilder(InjectDefinition::class)->getMock()
                ],
                fn(InjectDefinition $injectDefinition) =>
                    $this->subject->emitAddedInjectDefinitionFromApi($injectDefinition)
            ],
            AddedServiceDefinitionFromApi::class => [
                AddedServiceDefinitionFromApi::class,
                fn() => [
                    $this->getMockBuilder(ServiceDefinition::class)->getMock()
                ],
                fn(ServiceDefinition $serviceDefinition) =>
                    $this->subject->emitAddedServiceDefinitionFromApi($serviceDefinition)
            ],
            AddedServiceDelegateDefinitionFromApi::class => [
                AddedServiceDelegateDefinitionFromApi::class,
                fn() => [
                    $this->getMockBuilder(ServiceDelegateDefinition::class)->getMock()
                ],
                fn(ServiceDelegateDefinition $serviceDelegateDefinition) =>
                    $this->subject->emitAddedServiceDelegateDefinitionFromApi($serviceDelegateDefinition)
            ],
            AddedServicePrepareDefinitionFromApi::class => [
                AddedServicePrepareDefinitionFromApi::class,
                fn() => [
                    $this->getMockBuilder(ServicePrepareDefinition::class)->getMock()
                ],
                fn(ServicePrepareDefinition $servicePrepareDefinition) =>
                    $this->subject->emitAddedServicePrepareDefinitionFromApi($servicePrepareDefinition)
            ],
            AnalyzedContainerDefinitionFromCache::class => [
                AnalyzedContainerDefinitionFromCache::class,
                fn() => [
                    $this->getMockBuilder(ContainerDefinition::class)->getMock(),
                    '/app/cache-file'
                ],
                fn(ContainerDefinition $containerDefinition, string $cacheFile) =>
                    $this->subject->emitAnalyzedContainerDefinitionFromCache($containerDefinition, $cacheFile)
            ],
            AfterContainerAnalysis::class => [
                AfterContainerAnalysis::class,
                fn() => [
                    $this->getMockBuilder(ContainerDefinitionAnalysisOptions::class)->getMock(),
                    $this->getMockBuilder(ContainerDefinition::class)->getMock(),
                ],
                fn(ContainerDefinitionAnalysisOptions $analysisOptions, ContainerDefinition $containerDefinition) =>
                    $this->subject->emitAfterContainerAnalysis($analysisOptions, $containerDefinition)
            ],
            BeforeContainerCreation::class => [
                BeforeContainerCreation::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(ContainerDefinition::class)->getMock()
                ],
                fn(Profiles $profiles, ContainerDefinition $containerDefinition) =>
                    $this->subject->emitBeforeContainerCreation($profiles, $containerDefinition)
            ],
            ServiceFilteredDueToProfiles::class => [
                ServiceFilteredDueToProfiles::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(ServiceDefinition::class)->getMock()
                ],
                fn(Profiles $profiles, ServiceDefinition $serviceDefinition) =>
                    $this->subject->emitServiceFilteredDueToProfiles($profiles, $serviceDefinition)
            ],
            ServiceShared::class => [
                ServiceShared::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(ServiceDefinition::class)->getMock()
                ],
                fn(Profiles $profiles, ServiceDefinition $serviceDefinition) =>
                    $this->subject->emitServiceShared($profiles, $serviceDefinition)
            ],
            InjectingMethodParameter::class => [
                InjectingMethodParameter::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(InjectDefinition::class)->getMock()
                ],
                fn(Profiles $profiles, InjectDefinition $injectDefinition) =>
                    $this->subject->emitInjectingMethodParameter($profiles, $injectDefinition)
            ],
            ServicePrepared::class => [
                ServicePrepared::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(ServicePrepareDefinition::class)->getMock()
                ],
                fn(Profiles $profiles, ServicePrepareDefinition $servicePrepareDefinition) =>
                    $this->subject->emitServicePrepared($profiles, $servicePrepareDefinition)
            ],
            ServiceDelegated::class => [
                ServiceDelegated::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(ServiceDelegateDefinition::class)->getMock()
                ],
                fn(Profiles $profiles, ServiceDelegateDefinition $serviceDelegateDefinition) =>
                    $this->subject->emitServiceDelegated($profiles, $serviceDelegateDefinition)
            ],
            ServiceAliasResolution::class => [
                ServiceAliasResolution::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(AliasDefinition::class)->getMock(),
                    AliasResolutionReason::ConcreteServiceIsPrimary
                ],
                fn(Profiles $profiles, AliasDefinition $definition, AliasResolutionReason $resolutionReason) =>
                    $this->subject->emitServiceAliasResolution($profiles, $definition, $resolutionReason)
            ],
            AfterContainerCreation::class => [
                AfterContainerCreation::class,
                fn() => [
                    Profiles::fromList(['default']),
                    $this->getMockBuilder(ContainerDefinition::class)->getMock(),
                    $this->getMockBuilder(AnnotatedContainer::class)->getMock()
                ],
                fn(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) =>
                    $this->subject->emitAfterContainerCreation($profiles, $containerDefinition, $container)
            ],
            AfterBootstrap::class => [
                AfterBootstrap::class,
                fn() => [
                    $this->getMockBuilder(BootstrappingConfiguration::class)->getMock(),
                    $this->getMockBuilder(ContainerDefinition::class)->getMock(),
                    $this->getMockBuilder(AnnotatedContainer::class)->getMock(),
                    new ContainerAnalytics(
                        new Duration(0, 0),
                        new Duration(0, 0),
                        new Duration(0, 0),
                        new Duration(0, 0)
                    )
                ],
                fn(BootstrappingConfiguration $bootstrappingConfiguration, ContainerDefinition $containerDefinition, AnnotatedContainer $container, ContainerAnalytics $containerAnalytics) =>
                    $this->subject->emitAfterBootstrap($bootstrappingConfiguration, $containerDefinition, $container, $containerAnalytics)
            ]
        ];
    }

    /**
     * @param class-string<Listener> $listenerClass
     * @param Closure $handleArgs
     * @param Closure $emitEvent
     * @return void
     */
    #[DataProvider('listenerData')]
    public function testAddedListenerInvokedWithCorrectParameters(
        string $listenerClass,
        Closure $handleArgs,
        Closure $emitEvent
    ) : void {
        $listener = $this->getMockBuilder($listenerClass)->getMock();

        $args = $handleArgs->call($this);

        $listener->expects($this->once())
            ->method('handle' . (new \ReflectionClass($listenerClass))->getShortName())
            ->with(...$args);

        $this->subject->addListener($listener);
        $emitEvent->call($this, ...$args);
    }

    public function testAddListenerNotKnownTypeThrowsException() : void {
        $listener = $this->getMockBuilder(Listener::class)->getMock();

        $expectedMessage = sprintf(
            '%s implements %s but is not a known types provided by Annotated Container. You should not implement' .
            ' the %s interface directly, instead choosing to use a specific interface under the ' .
            'Cspray\\AnnotatedContainer\\Event\\Listener namespace.',
            $listener::class,
            Listener::class,
            Listener::class
        );

        $this->expectException(InvalidListener::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->subject->addListener($listener);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\StaticAnalysis\CallableDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceDelegate;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServicePrepare;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDelegateDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServicePrepareDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use Cspray\AnnotatedContainer\Fixture\ThirdPartyKitchenSink\NonAnnotatedInterface;
use Cspray\AnnotatedContainer\Fixture\ThirdPartyKitchenSink\NonAnnotatedService;
use function Cspray\AnnotatedContainer\Definition\inject;
use function Cspray\AnnotatedContainer\Definition\service;
use function Cspray\AnnotatedContainer\Definition\serviceDelegate;
use function Cspray\AnnotatedContainer\Definition\servicePrepare;
use function Cspray\AnnotatedContainer\Reflection\types;

class ThirdPartyEventEmittingTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasServiceDelegateDefinitionTestsTrait,
        HasServicePrepareDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::thirdPartyKitchenSink();
    }

    protected function getDefinitionProvider() : ?DefinitionProvider {
        return new CallableDefinitionProvider(static function(DefinitionProviderContext $context) {
            $context->addServiceDefinition(service(types()->class(NonAnnotatedInterface::class)));
            $context->addServiceDefinition(service(types()->class(NonAnnotatedService::class)));
            $context->addServiceDelegateDefinition(
                serviceDelegate(
                    types()->class(NonAnnotatedService::class),
                    'create'
                )
            );
            $context->addServicePrepareDefinition(
                servicePrepare(
                    types()->class(NonAnnotatedService::class),
                    'init'
                )
            );
            $context->addInjectDefinition(
                inject(
                    types()->class(NonAnnotatedService::class),
                    'init',
                    'value',
                    types()->string(),
                    'calledFromApi'
                )
            );
        });
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(8, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(2, $analysisEventCollection->filter(AnalysisEvent::AddedServiceDefinitionFromApi));
        self::assertCount(1, $analysisEventCollection->filter(AnalysisEvent::AddedServiceDelegateDefinitionFromApi));
        self::assertCount(1, $analysisEventCollection->filter(AnalysisEvent::AddedServicePrepareDefinitionFromApi));
        self::assertCount(1, $analysisEventCollection->filter(AnalysisEvent::AddedInjectDefinitionFromApi));
        self::assertCount(1, $analysisEventCollection->filter(AnalysisEvent::AddedAliasDefinition));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(types()->class(NonAnnotatedInterface::class), types()->class(NonAnnotatedService::class))]
        ];
    }

    public static function injectProvider() : array {
        return [
            [ExpectedInject::forMethodParam(
                types()->class(NonAnnotatedService::class),
                'init',
                'value',
                types()->string(),
                'calledFromApi'
            )]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(types()->class(NonAnnotatedInterface::class))],
            [new ExpectedServiceType(types()->class(NonAnnotatedService::class))],
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(types()->class(NonAnnotatedInterface::class), null)],
            [new ExpectedServiceName(types()->class(NonAnnotatedService::class), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(types()->class(NonAnnotatedInterface::class), false)],
            [new ExpectedServiceIsPrimary(types()->class(NonAnnotatedService::class), false)],
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(types()->class(NonAnnotatedInterface::class), false)],
            [new ExpectedServiceIsConcrete(types()->class(NonAnnotatedService::class), true)],
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(types()->class(NonAnnotatedInterface::class), true)],
            [new ExpectedServiceIsAbstract(types()->class(NonAnnotatedService::class), false)],
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(types()->class(NonAnnotatedInterface::class), ['default'])],
            [new ExpectedServiceProfiles(types()->class(NonAnnotatedService::class), ['default'])],
        ];
    }

    public static function serviceDelegateProvider() : array {
        return [
            [new ExpectedServiceDelegate(types()->class(NonAnnotatedService::class), types()->class(NonAnnotatedService::class), 'create')],
        ];
    }

    public static function servicePrepareProvider() : array {
        return [
            [new ExpectedServicePrepare(types()->class(NonAnnotatedService::class), 'init')]
        ];
    }
}

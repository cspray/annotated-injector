<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\StaticAnalysis\CallableDefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceDelegate;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServicePrepare;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDelegateDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServicePrepareDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use Cspray\AnnotatedContainerFixture\ThirdPartyKitchenSink\NonAnnotatedInterface;
use Cspray\AnnotatedContainerFixture\ThirdPartyKitchenSink\NonAnnotatedService;
use function Cspray\AnnotatedContainer\inject;
use function Cspray\AnnotatedContainer\service;
use function Cspray\AnnotatedContainer\serviceDelegate;
use function Cspray\AnnotatedContainer\servicePrepare;
use function Cspray\Typiphy\objectType;
use function Cspray\Typiphy\stringType;

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
            $context->addServiceDefinition(service(objectType(NonAnnotatedInterface::class)));
            $context->addServiceDefinition(service(objectType(NonAnnotatedService::class)));
            $context->addServiceDelegateDefinition(
                serviceDelegate(
                    objectType(NonAnnotatedService::class),
                    objectType(NonAnnotatedService::class),
                    'create'
                )
            );
            $context->addServicePrepareDefinition(
                servicePrepare(
                    objectType(NonAnnotatedService::class),
                    'init'
                )
            );
            $context->addInjectDefinition(
                inject(
                    objectType(NonAnnotatedService::class),
                    'init',
                    'value',
                    stringType(),
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
            [new ExpectedAliasDefinition(objectType(NonAnnotatedInterface::class), objectType(NonAnnotatedService::class))]
        ];
    }

    public static function injectProvider() : array {
        return [
            [ExpectedInject::forMethodParam(
                objectType(NonAnnotatedService::class),
                'init',
                'value',
                stringType(),
                'calledFromApi'
            )]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(objectType(NonAnnotatedInterface::class))],
            [new ExpectedServiceType(objectType(NonAnnotatedService::class))],
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(objectType(NonAnnotatedInterface::class), null)],
            [new ExpectedServiceName(objectType(NonAnnotatedService::class), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(objectType(NonAnnotatedInterface::class), false)],
            [new ExpectedServiceIsPrimary(objectType(NonAnnotatedService::class), false)],
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(objectType(NonAnnotatedInterface::class), false)],
            [new ExpectedServiceIsConcrete(objectType(NonAnnotatedService::class), true)],
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(objectType(NonAnnotatedInterface::class), true)],
            [new ExpectedServiceIsAbstract(objectType(NonAnnotatedService::class), false)],
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(objectType(NonAnnotatedInterface::class), ['default'])],
            [new ExpectedServiceProfiles(objectType(NonAnnotatedService::class), ['default'])],
        ];
    }

    public static function serviceDelegateProvider() : array {
        return [
            [new ExpectedServiceDelegate(objectType(NonAnnotatedService::class), objectType(NonAnnotatedService::class), 'create')],
        ];
    }

    public static function servicePrepareProvider() : array {
        return [
            [new ExpectedServicePrepare(objectType(NonAnnotatedService::class), 'init')]
        ];
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServicePrepare;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServicePrepareDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeUnion;

class InjectPrepareServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasServicePrepareDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::injectPrepareServices();
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::injectPrepareServices()->fooInterface(), Fixtures::injectPrepareServices()->fooImplementation())],
            [new ExpectedAliasDefinition(Fixtures::injectPrepareServices()->fooInterface(), Fixtures::injectPrepareServices()->barImplementation())]
        ];
    }

    public static function injectProvider() : array {
        return [
            [ExpectedInject::forMethodParam(
                Fixtures::injectPrepareServices()->prepareInjector(),
                'setVals',
                'val',
                stringType(),
                'foo'
            )],
            [ExpectedInject::forMethodParam(
                Fixtures::injectPrepareServices()->prepareInjector(),
                'setVals',
                'service',
                Fixtures::injectPrepareServices()->fooInterface(),
                Fixtures::injectPrepareServices()->barImplementation()->getName()
            )],
            [ExpectedInject::forMethodParam(
                Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(),
                'setValue',
                'val',
                typeUnion(floatType(), Fixtures::injectPrepareServices()->fooInterface()),
                3.14
            )]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->fooImplementation())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->prepareInjector())],
            [new ExpectedServiceType(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->fooImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->prepareInjector(), null)],
            [new ExpectedServiceName(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->prepareInjector(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->prepareInjector(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->prepareInjector(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), false)]
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->prepareInjector(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), ['default'])]
        ];
    }

    public static function servicePrepareProvider() : array {
        return [
            [new ExpectedServicePrepare(Fixtures::injectPrepareServices()->prepareInjector(), 'setVals')],
            [new ExpectedServicePrepare(Fixtures::injectPrepareServices()->serviceScalarUnionPrepareInjector(), 'setValue')]
        ];
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(14, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(5, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute));
        self::assertCount(2, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServicePrepareDefinitionFromAttribute));
        self::assertCount(3, $analysisEventCollection->filter(AnalysisEvent::AnalyzedInjectDefinitionFromAttribute));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }
}

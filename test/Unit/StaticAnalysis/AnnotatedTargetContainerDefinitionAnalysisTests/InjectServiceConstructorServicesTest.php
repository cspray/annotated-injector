<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use function Cspray\Typiphy\nullType;
use function Cspray\Typiphy\typeUnion;

class InjectServiceConstructorServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::injectServiceConstructorServices();
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::injectServiceConstructorServices()->fooInterface(), Fixtures::injectServiceConstructorServices()->fooImplementation())],
            [new ExpectedAliasDefinition(Fixtures::injectServiceConstructorServices()->fooInterface(), Fixtures::injectServiceConstructorServices()->barImplementation())]
        ];
    }

    public static function injectProvider() : array {
        return [
            [ExpectedInject::forConstructParam(
                Fixtures::injectServiceConstructorServices()->serviceInjector(),
                'foo',
                Fixtures::injectServiceConstructorServices()->fooInterface(),
                Fixtures::injectServiceConstructorServices()->fooImplementation()->getName()
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectServiceConstructorServices()->nullableServiceInjector(),
                'maybeFoo',
                typeUnion(nullType(), Fixtures::injectServiceConstructorServices()->fooInterface()),
                null
            )]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->fooImplementation())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->serviceInjector())],
            [new ExpectedServiceType(Fixtures::injectServiceConstructorServices()->nullableServiceInjector())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->fooImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->serviceInjector(), null)],
            [new ExpectedServiceName(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->serviceInjector(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->serviceInjector(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->serviceInjector(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), false)]
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->serviceInjector(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectServiceConstructorServices()->nullableServiceInjector(), ['default'])]
        ];
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(11, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(5, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute));
        self::assertCount(2, $analysisEventCollection->filter(AnalysisEvent::AnalyzedInjectDefinitionFromAttribute));
        self::assertCount(2, $analysisEventCollection->filter(AnalysisEvent::AddedAliasDefinition));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class PrimaryAliasedServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::primaryAliasedServices();
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::primaryAliasedServices()->fooInterface(), Fixtures::primaryAliasedServices()->fooImplementation())],
            [new ExpectedAliasDefinition(Fixtures::primaryAliasedServices()->fooInterface(), Fixtures::primaryAliasedServices()->barImplementation())],
            [new ExpectedAliasDefinition(Fixtures::primaryAliasedServices()->fooInterface(), Fixtures::primaryAliasedServices()->bazImplementation())]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->bazImplementation())],
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::primaryAliasedServices()->fooImplementation())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->bazImplementation(), null)],
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::primaryAliasedServices()->fooImplementation(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::primaryAliasedServices()->bazImplementation(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::primaryAliasedServices()->bazImplementation(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::primaryAliasedServices()->bazImplementation(), false)]
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::primaryAliasedServices()->bazImplementation(), ['default'])]
        ];
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(9, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(4, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute));
        self::assertCount(3, $analysisEventCollection->filter(AnalysisEvent::AddedAliasDefinition));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }
}

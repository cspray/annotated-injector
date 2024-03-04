<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class MultipleAliasedServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::ambiguousAliasedServices();
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->barImplementation())],
            [new ExpectedAliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->bazImplementation())],
            [new ExpectedAliasDefinition(Fixtures::ambiguousAliasedServices()->fooInterface(), Fixtures::ambiguousAliasedServices()->quxImplementation())]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->bazImplementation())],
            [new ExpectedServiceType(Fixtures::ambiguousAliasedServices()->quxImplementation())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->bazImplementation(), null)],
            [new ExpectedServiceName(Fixtures::ambiguousAliasedServices()->quxImplementation(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->bazImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::ambiguousAliasedServices()->quxImplementation(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->bazImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::ambiguousAliasedServices()->quxImplementation(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::ambiguousAliasedServices()->quxImplementation(), false)]
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->bazImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::ambiguousAliasedServices()->quxImplementation(), ['default'])],
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

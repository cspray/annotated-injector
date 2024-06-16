<?php

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
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class CustomServiceAttributeTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::customServiceAttribute();
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::customServiceAttribute()->repo())],
            [new ExpectedServiceType(Fixtures::customServiceAttribute()->myRepo())],
            [new ExpectedServiceType(Fixtures::customServiceAttribute()->otherService())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::customServiceAttribute()->repo(), null)],
            [new ExpectedServiceName(Fixtures::customServiceAttribute()->myRepo(), null)],
            [new ExpectedServiceName(Fixtures::customServiceAttribute()->otherService(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::customServiceAttribute()->repo(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::customServiceAttribute()->myRepo(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::customServiceAttribute()->otherService(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::customServiceAttribute()->repo(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::customServiceAttribute()->myRepo(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::customServiceAttribute()->otherService(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::customServiceAttribute()->repo(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::customServiceAttribute()->myRepo(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::customServiceAttribute()->otherService(), false)],
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::customServiceAttribute()->repo(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::customServiceAttribute()->myRepo(), ['test'])],
            [new ExpectedServiceProfiles(Fixtures::customServiceAttribute()->otherService(), ['default'])],
        ];
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(6, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(3, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute));
        self::assertCount(1, $analysisEventCollection->filter(AnalysisEvent::AddedAliasDefinition));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(
                Fixtures::customServiceAttribute()->repo(),
                Fixtures::customServiceAttribute()->myRepo()
            )]
        ];
    }
}

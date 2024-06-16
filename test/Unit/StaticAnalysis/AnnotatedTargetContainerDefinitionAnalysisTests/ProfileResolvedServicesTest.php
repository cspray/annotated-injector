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
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\Fixtures;

class ProfileResolvedServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::profileResolvedServices();
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->devImplementation())],
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->testImplementation())],
            [new ExpectedServiceType(Fixtures::profileResolvedServices()->prodImplementation())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->devImplementation(), null)],
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->testImplementation(), null)],
            [new ExpectedServiceName(Fixtures::profileResolvedServices()->prodImplementation(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->devImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->testImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::profileResolvedServices()->prodImplementation(), false)],
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->devImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->testImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::profileResolvedServices()->prodImplementation(), true)],
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->devImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->testImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::profileResolvedServices()->prodImplementation(), false)],
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->devImplementation(), ['dev'])],
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->testImplementation(), ['test'])],
            [new ExpectedServiceProfiles(Fixtures::profileResolvedServices()->prodImplementation(), ['prod'])],
        ];
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::profileResolvedServices()->fooInterface(), Fixtures::profileResolvedServices()->devImplementation())],
            [new ExpectedAliasDefinition(Fixtures::profileResolvedServices()->fooInterface(), Fixtures::profileResolvedServices()->testImplementation())],
            [new ExpectedAliasDefinition(Fixtures::profileResolvedServices()->fooInterface(), Fixtures::profileResolvedServices()->prodImplementation())]
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

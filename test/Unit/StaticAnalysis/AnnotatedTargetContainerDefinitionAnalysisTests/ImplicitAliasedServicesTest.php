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

class ImplicitAliasedServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait;

    protected function getFixtures() : Fixture {
        return Fixtures::implicitAliasedServices();
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::implicitAliasedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::implicitAliasedServices()->fooImplementation())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::implicitAliasedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::implicitAliasedServices()->fooImplementation(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::implicitAliasedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::implicitAliasedServices()->fooImplementation(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::implicitAliasedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::implicitAliasedServices()->fooInterface(), false)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::implicitAliasedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::implicitAliasedServices()->fooInterface(), true)]
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::implicitAliasedServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::implicitAliasedServices()->fooInterface(), ['default'])]
        ];
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::implicitAliasedServices()->fooInterface(), Fixtures::implicitAliasedServices()->fooImplementation())]
        ];
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(5, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(2, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute));
        self::assertCount(1, $analysisEventCollection->filter(AnalysisEvent::AddedAliasDefinition));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }
}

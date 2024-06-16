<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\Fixtures;

class NonAnnotatedServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait;

    use HasNoAliasDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoInjectDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::nonAnnotatedServices();
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::nonAnnotatedServices()->annotatedService())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::nonAnnotatedServices()->annotatedService(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::nonAnnotatedServices()->annotatedService(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::nonAnnotatedServices()->annotatedService(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::nonAnnotatedServices()->annotatedService(), false)]
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::nonAnnotatedServices()->annotatedService(), ['default'])]
        ];
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(3, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(1, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }
}

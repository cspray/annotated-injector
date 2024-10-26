<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEvent;
use Cspray\AnnotatedContainer\Unit\Helper\AnalysisEventCollection;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use function Cspray\AnnotatedContainer\Reflection\types;

class InjectConstructorServicesTest extends AnnotatedTargetContainerDefinitionAnalyzerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoAliasDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::injectConstructorServices();
    }

    public static function injectProvider() : array {
        return [
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectArrayService(),
                'values',
                types()->array(),
                ['dependency', 'injection', 'rocks']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectIntService(),
                'meaningOfLife',
                types()->int(),
                42
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectBoolService(),
                'flag',
                types()->bool(),
                false
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectFloatService(),
                'dessert',
                types()->float(),
                3.14
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectStringService(),
                'val',
                types()->string(),
                'foobar'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectEnvService(),
                'user',
                types()->string(),
                'USER',
                store: 'env'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectExplicitMixedService(),
                'value',
                types()->mixed(),
                'whatever'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectImplicitMixedService(),
                'val',
                types()->mixed(),
                'something'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectNullableStringService(),
                'maybe',
                types()->nullable(types()->string()),
                null
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectProfilesStringService(),
                'val',
                types()->string(),
                'from-dev',
                ['dev']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectProfilesStringService(),
                'val',
                types()->string(),
                'from-test',
                ['test']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectProfilesStringService(),
                'val',
                types()->string(),
                'from-prod',
                ['prod']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectTypeUnionService(),
                'value',
                types()->union(types()->string(), types()->int(), types()->float()),
                4.20
            )]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectArrayService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectIntService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectBoolService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectFloatService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectStringService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectEnvService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectExplicitMixedService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectImplicitMixedService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectNullableStringService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectProfilesStringService())],
            [new ExpectedServiceType(Fixtures::injectConstructorServices()->injectTypeUnionService())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectArrayService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectIntService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectBoolService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectFloatService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectStringService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectEnvService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectExplicitMixedService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectImplicitMixedService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectNullableStringService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectProfilesStringService(), null)],
            [new ExpectedServiceName(Fixtures::injectConstructorServices()->injectTypeUnionService(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectArrayService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectIntService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectBoolService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectFloatService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectStringService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectEnvService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectExplicitMixedService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectImplicitMixedService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectNullableStringService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectProfilesStringService(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectConstructorServices()->injectTypeUnionService(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectArrayService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectIntService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectBoolService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectFloatService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectStringService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectEnvService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectExplicitMixedService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectImplicitMixedService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectNullableStringService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectProfilesStringService(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectConstructorServices()->injectTypeUnionService(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectArrayService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectIntService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectBoolService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectFloatService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectStringService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectEnvService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectExplicitMixedService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectImplicitMixedService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectNullableStringService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectProfilesStringService(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectConstructorServices()->injectTypeUnionService(), false)]
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectArrayService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectIntService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectBoolService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectFloatService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectStringService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectEnvService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectExplicitMixedService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectImplicitMixedService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectNullableStringService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectProfilesStringService(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectConstructorServices()->injectTypeUnionService(), ['default'])]
        ];
    }

    protected function assertEmittedEvents(AnalysisEventCollection $analysisEventCollection) : void {
        self::assertCount(26, $analysisEventCollection);
        self::assertSame(AnalysisEvent::BeforeContainerAnalysis, $analysisEventCollection->first());
        self::assertCount(11, $analysisEventCollection->filter(AnalysisEvent::AnalyzedServiceDefinitionFromAttribute));
        self::assertCount(13, $analysisEventCollection->filter(AnalysisEvent::AnalyzedInjectDefinitionFromAttribute));
        self::assertSame(AnalysisEvent::AfterContainerAnalysis, $analysisEventCollection->last());
    }
}

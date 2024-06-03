<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\arrayType;
use function Cspray\Typiphy\boolType;
use function Cspray\Typiphy\floatType;
use function Cspray\Typiphy\intType;
use function Cspray\Typiphy\mixedType;
use function Cspray\Typiphy\nullType;
use function Cspray\Typiphy\stringType;
use function Cspray\Typiphy\typeUnion;

class InjectConstructorServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait,
        HasNoAliasDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::injectConstructorServices();
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectArrayService(),
                'values',
                arrayType(),
                ['dependency', 'injection', 'rocks']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectIntService(),
                'meaningOfLife',
                intType(),
                42
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectBoolService(),
                'flag',
                boolType(),
                false
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectFloatService(),
                'dessert',
                floatType(),
                3.14
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectStringService(),
                'val',
                stringType(),
                'foobar'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectEnvService(),
                'user',
                stringType(),
                'USER',
                store: 'env'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectExplicitMixedService(),
                'value',
                mixedType(),
                'whatever'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectImplicitMixedService(),
                'val',
                mixedType(),
                'something'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectNullableStringService(),
                'maybe',
                typeUnion(nullType(), stringType()),
                null
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectProfilesStringService(),
                'val',
                stringType(),
                'from-dev',
                ['dev']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectProfilesStringService(),
                'val',
                stringType(),
                'from-test',
                ['test']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectProfilesStringService(),
                'val',
                stringType(),
                'from-prod',
                ['prod']
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectConstructorServices()->injectTypeUnionService(),
                'value',
                typeUnion(stringType(), intType(), floatType()),
                4.20
            )]
        ];
    }

    protected function serviceTypeProvider() : array {
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

    protected function serviceNameProvider() : array {
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

    protected function serviceIsPrimaryProvider() : array {
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

    protected function serviceIsConcreteProvider() : array {
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

    protected function serviceIsAbstractProvider() : array {
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

    protected function serviceProfilesProvider() : array {
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
}

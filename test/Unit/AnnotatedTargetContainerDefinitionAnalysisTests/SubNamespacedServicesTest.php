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
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoInjectDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class SubNamespacedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasServiceDefinitionTestsTrait,
        HasAliasDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoInjectDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::subNamespacedServices();
    }

    public static function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::subNamespacedServices()->barInterface(), Fixtures::subNamespacedServices()->barImplementation())],
            [new ExpectedAliasDefinition(Fixtures::subNamespacedServices()->bazInterface(), Fixtures::subNamespacedServices()->bazImplementation())],
            [new ExpectedAliasDefinition(Fixtures::subNamespacedServices()->fooInterface(), Fixtures::subNamespacedServices()->fooImplementation())]
        ];
    }

    public static function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->barInterface())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->bazInterface())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->bazImplementation())],
            [new ExpectedServiceType(Fixtures::subNamespacedServices()->fooImplementation())]
        ];
    }

    public static function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->barInterface(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->bazInterface(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->fooImplementation(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->barImplementation(), null)],
            [new ExpectedServiceName(Fixtures::subNamespacedServices()->bazImplementation(), null)]
        ];
    }

    public static function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->barInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->bazInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::subNamespacedServices()->bazImplementation(), false)]
        ];
    }

    public static function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->barInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->bazInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::subNamespacedServices()->bazImplementation(), true)]
        ];
    }

    public static function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->barInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->bazInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::subNamespacedServices()->bazImplementation(), false)],
        ];
    }

    public static function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->barInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->bazInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->bazImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::subNamespacedServices()->fooImplementation(), ['default'])],
        ];
    }
}

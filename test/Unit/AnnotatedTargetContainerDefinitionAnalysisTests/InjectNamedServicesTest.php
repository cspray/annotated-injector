<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedAliasDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsAbstract;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsConcrete;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceIsPrimary;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceProfiles;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasAliasDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoConfigurationDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasServiceDefinitionTestsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;

class InjectNamedServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasAliasDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait,
        HasServiceDefinitionTestsTrait;

    use HasNoServiceDelegateDefinitionsTrait,
        HasNoConfigurationDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait;


    protected function getFixtures() : array|Fixture {
        return Fixtures::injectNamedServices();
    }

    protected function aliasProvider() : array {
        return [
            [new ExpectedAliasDefinition(Fixtures::injectNamedServices()->fooInterface(), Fixtures::injectNamedServices()->barImplementation())],
            [new ExpectedAliasDefinition(Fixtures::injectNamedServices()->fooInterface(), Fixtures::injectNamedServices()->fooImplementation())]
        ];
    }

    protected function injectProvider() : array {
        return [
            [ExpectedInject::forConstructParam(
                Fixtures::injectNamedServices()->serviceConsumer(),
                'foo',
                Fixtures::injectNamedServices()->fooInterface(),
                'foo'
            )],
            [ExpectedInject::forConstructParam(
                Fixtures::injectNamedServices()->serviceConsumer(),
                'bar',
                Fixtures::injectNamedServices()->fooInterface(),
                'bar'
            )]
        ];
    }

    protected function serviceTypeProvider() : array {
        return [
            [new ExpectedServiceType(Fixtures::injectNamedServices()->fooInterface())],
            [new ExpectedServiceType(Fixtures::injectNamedServices()->fooImplementation())],
            [new ExpectedServiceType(Fixtures::injectNamedServices()->barImplementation())],
            [new ExpectedServiceType(Fixtures::injectNamedServices()->serviceConsumer())]
        ];
    }

    protected function serviceNameProvider() : array {
        return [
            [new ExpectedServiceName(Fixtures::injectNamedServices()->fooInterface(), null)],
            [new ExpectedServiceName(Fixtures::injectNamedServices()->fooImplementation(), 'foo')],
            [new ExpectedServiceName(Fixtures::injectNamedServices()->barImplementation(), 'bar')],
            [new ExpectedServiceName(Fixtures::injectNamedServices()->serviceConsumer(), null)]
        ];
    }

    protected function serviceIsPrimaryProvider() : array {
        return [
            [new ExpectedServiceIsPrimary(Fixtures::injectNamedServices()->fooInterface(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectNamedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectNamedServices()->barImplementation(), false)],
            [new ExpectedServiceIsPrimary(Fixtures::injectNamedServices()->serviceConsumer(), false)]
        ];
    }

    protected function serviceIsConcreteProvider() : array {
        return [
            [new ExpectedServiceIsConcrete(Fixtures::injectNamedServices()->fooInterface(), false)],
            [new ExpectedServiceIsConcrete(Fixtures::injectNamedServices()->fooImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectNamedServices()->barImplementation(), true)],
            [new ExpectedServiceIsConcrete(Fixtures::injectNamedServices()->serviceConsumer(), true)]
        ];
    }

    protected function serviceIsAbstractProvider() : array {
        return [
            [new ExpectedServiceIsAbstract(Fixtures::injectNamedServices()->fooInterface(), true)],
            [new ExpectedServiceIsAbstract(Fixtures::injectNamedServices()->fooImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectNamedServices()->barImplementation(), false)],
            [new ExpectedServiceIsAbstract(Fixtures::injectNamedServices()->serviceConsumer(), false)]
        ];
    }

    protected function serviceProfilesProvider() : array {
        return [
            [new ExpectedServiceProfiles(Fixtures::injectNamedServices()->fooInterface(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectNamedServices()->fooImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectNamedServices()->barImplementation(), ['default'])],
            [new ExpectedServiceProfiles(Fixtures::injectNamedServices()->serviceConsumer(), ['default'])]
        ];
    }
}

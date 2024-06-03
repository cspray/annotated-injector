<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests;

use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedConfigurationName;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedConfigurationType;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasConfigurationDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasInjectDefinitionTestsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoAliasDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServiceDelegateDefinitionsTrait;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait\HasNoServicePrepareDefinitionsTrait;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\stringType;

class MultiPropConfigurationServicesTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasConfigurationDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoServicePrepareDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoAliasDefinitionsTrait,
        HasNoServiceDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::multiPropConfigurationServices();
    }

    public static function configurationTypeProvider() : array {
        return [
            [new ExpectedConfigurationType(Fixtures::multiPropConfigurationServices()->myConfig())]
        ];
    }

    public static function configurationNameProvider() : array {
        return [
            [new ExpectedConfigurationName(Fixtures::multiPropConfigurationServices()->myConfig(), null)]
        ];
    }

    public static function injectProvider() : array {
        return [
            [ExpectedInject::forClassProperty(
                Fixtures::multiPropConfigurationServices()->myConfig(),
                'foo',
                stringType(),
                'baz'
            )],
            [ExpectedInject::forClassProperty(
                Fixtures::multiPropConfigurationServices()->myConfig(),
                'bar',
                stringType(),
                'baz'
            )]
        ];
    }
}

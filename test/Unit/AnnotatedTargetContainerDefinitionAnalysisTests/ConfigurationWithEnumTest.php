<?php

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
use Cspray\AnnotatedContainerFixture\ConfigurationWithEnum\MyEnum;
use Cspray\AnnotatedContainerFixture\Fixture;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\Typiphy\objectType;

class ConfigurationWithEnumTest extends AnnotatedTargetContainerDefinitionCompilerTestCase {

    use HasConfigurationDefinitionTestsTrait,
        HasInjectDefinitionTestsTrait;

    use HasNoServiceDefinitionsTrait,
        HasNoServiceDelegateDefinitionsTrait,
        HasNoServicePrepareDefinitionsTrait,
        HasNoAliasDefinitionsTrait;

    protected function getFixtures() : array|Fixture {
        return Fixtures::configurationWithEnum();
    }

    public static function configurationTypeProvider() : array {
        return [
            [new ExpectedConfigurationType(Fixtures::configurationWithEnum()->configuration())]
        ];
    }

    public static function configurationNameProvider() : array {
        return [
            [new ExpectedConfigurationName(Fixtures::configurationWithEnum()->configuration(), null)]
        ];
    }

    public static function injectProvider() : array {
        return [
            [ExpectedInject::forClassProperty(
                Fixtures::configurationWithEnum()->configuration(),
                'enum',
                objectType(MyEnum::class),
                Fixtures::configurationWithEnum()->fooEnum()
            )]
        ];
    }
}

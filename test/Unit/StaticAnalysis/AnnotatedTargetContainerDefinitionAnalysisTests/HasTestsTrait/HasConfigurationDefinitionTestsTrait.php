<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\ContainerDefinitionAssertionsTrait;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedConfigurationName;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedConfigurationType;
use PHPUnit\Framework\Attributes\DataProvider;

trait HasConfigurationDefinitionTestsTrait {

    use ContainerDefinitionAssertionsTrait;

    abstract protected function getSubject() : ContainerDefinition;

    abstract public static function configurationTypeProvider() : array;

    abstract public static function configurationNameProvider() : array;

    final public function testConfigurationTypeCount() : void {
        $expected = count($this->configurationTypeProvider());

        $this->assertSame($expected, count($this->getSubject()->getConfigurationDefinitions()));
    }

    final public function testConfigurationNameCount() : void {
        $expected = count($this->configurationNameProvider());

        $this->assertSame($expected, count($this->getSubject()->getConfigurationDefinitions()));
    }

    #[DataProvider('configurationTypeProvider')]
    final public function testConfigurationType(ExpectedConfigurationType $expectedConfigurationType) : void {
        $configurationDefinition = $this->getConfigurationDefinition($this->getSubject()->getConfigurationDefinitions(), $expectedConfigurationType->configuration->name());

        $this->assertNotNull($configurationDefinition);
    }

    #[DataProvider('configurationNameProvider')]
    final public function testConfigurationName(ExpectedConfigurationName $expectedConfigurationName) : void {
        $configurationDefinition = $this->getConfigurationDefinition($this->getSubject()->getConfigurationDefinitions(), $expectedConfigurationName->configuration->name());

        $this->assertSame($expectedConfigurationName->name, $configurationDefinition->name());
    }
}

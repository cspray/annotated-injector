<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ConfigurationDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;

trait ContainerDefinitionAssertionsTrait /** extends \PHPUnit\TestCase */ {

    protected function assertServiceDefinitionsHaveTypes(array $expectedTypes, array $serviceDefinitions) : void {
        if (($countExpected = count($expectedTypes)) !== ($countActual = count($serviceDefinitions))) {
            $this->fail("Expected $countExpected ServiceDefinitions but received $countActual");
        }

        $actualTypes = [];
        foreach ($serviceDefinitions as $serviceDefinition) {
            $this->assertInstanceOf(ServiceDefinition::class, $serviceDefinition);
            $actualTypes[] = $serviceDefinition->type();
        }

        $this->assertEqualsCanonicalizing($expectedTypes, $actualTypes);
    }

    protected function assertServiceDefinitionIsPrimary(array $serviceDefinitions, string $serviceDefinitionType) : void {
        $serviceDefinition = $this->getServiceDefinition($serviceDefinitions, $serviceDefinitionType);
        if ($serviceDefinition === null) {
            $this->fail("Expected $serviceDefinitionType to be present in the provided collection but it is not.");
        }

        $this->assertTrue($serviceDefinition->isPrimary());
    }

    protected function assertServiceDefinitionIsNotPrimary(array $serviceDefinitions, string $serviceDefinitionType) : void {
        $serviceDefinition = $this->getServiceDefinition($serviceDefinitions, $serviceDefinitionType);
        if ($serviceDefinition === null) {
            $this->fail("Expected $serviceDefinitionType to be present in the provided collection but it is not.");
        }

        $this->assertFalse($serviceDefinition->isPrimary());
    }

    protected function assertAliasDefinitionsMap(array $expectedAliasMap, array $aliasDefinitions) : void {
        if (($countExpected = count($expectedAliasMap)) !== ($countActual = count($aliasDefinitions))) {
            $this->fail("Expected $countExpected AliasDefinitions but received $countActual");
        }

        $actualMap = [];
        foreach ($aliasDefinitions as $aliasDefinition) {
            $this->assertInstanceOf(AliasDefinition::class, $aliasDefinition);
            $actualMap[] = [
                $aliasDefinition->abstractService()->getName(),
                $aliasDefinition->concreteService()->getName()
            ];
        }

        array_multisort($actualMap);
        array_multisort($expectedAliasMap);
        $this->assertEquals($expectedAliasMap, $actualMap);
    }

    protected function assertServicePrepareTypes(array $expectedServicePrepare, array $servicePrepareDefinitions) : void {
        if (($countExpected = count($expectedServicePrepare)) !== ($countActual = count($servicePrepareDefinitions))) {
            $this->fail("Expected $countExpected ServicePrepareDefinition but received $countActual");
        }

        $actualMap = [];
        foreach ($servicePrepareDefinitions as $servicePrepareDefinition) {
            $this->assertInstanceOf(ServicePrepareDefinition::class, $servicePrepareDefinition);
            $key = $servicePrepareDefinition->service()->getName();
            $actualMap[] = [$key, $servicePrepareDefinition->methodName()];
        }

        array_multisort($actualMap);
        array_multisort($expectedServicePrepare);
        $this->assertEquals($expectedServicePrepare, $actualMap);
    }

    /**
     * @param ServiceDefinition[] $serviceDefinitions
     * @param string $serviceDefinitionType
     * @return ServiceDefinition|null
     */
    protected function getServiceDefinition(array $serviceDefinitions, string $serviceDefinitionType) : ?ServiceDefinition {
        foreach ($serviceDefinitions as $serviceDefinition) {
            if ($serviceDefinitionType === $serviceDefinition->type()->getName()) {
                return $serviceDefinition;
            }
        }

        return null;
    }
}

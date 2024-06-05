<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServicePrepare;
use PHPUnit\Framework\Attributes\DataProvider;

trait HasServicePrepareDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract public static function servicePrepareProvider() : array;

    final public function testServicePrepareDefinitionsCount() : void {
        $expectedCount = count($this->servicePrepareProvider());

        $this->assertSame($expectedCount, count($this->getSubject()->servicePrepareDefinitions()));
    }

    #[DataProvider('servicePrepareProvider')]
    final public function testServicePrepareDefinitionMethod(ExpectedServicePrepare $expectedServicePrepare) : void {
        $preparesForService = array_filter(
            $this->getSubject()->servicePrepareDefinitions(),
            fn(ServicePrepareDefinition $servicePrepareDefinition) => $servicePrepareDefinition->service() === $expectedServicePrepare->type
        );
        $prepareMethods = array_map(
            fn(ServicePrepareDefinition $servicePrepareDefinition) => $servicePrepareDefinition->methodName(),
            $preparesForService
        );

        $this->assertContains($expectedServicePrepare->method, $prepareMethods);
    }
}

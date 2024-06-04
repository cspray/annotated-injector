<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedServiceDelegate;
use PHPUnit\Framework\Attributes\DataProvider;

trait HasServiceDelegateDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract public static function serviceDelegateProvider() : array;

    #[DataProvider('serviceDelegateProvider')]
    final public function testServiceDelegateDefinition(ExpectedServiceDelegate $expectedServiceDelegate) : void {
        $definition = null;
        foreach ($this->getSubject()->serviceDelegateDefinitions() as $delegateDefinition) {
            if ($delegateDefinition->getServiceType() === $expectedServiceDelegate->service) {
                $definition = $delegateDefinition;
                break;
            }
        }

        $this->assertSame($expectedServiceDelegate->factory, $definition?->getDelegateType());
        $this->assertSame($expectedServiceDelegate->method, $definition?->getDelegateMethod());
    }
}

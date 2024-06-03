<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;

trait HasInjectDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract protected function injectProvider() : array;

    final public function testInjectDefinitionCount() : void {
        $expectedCount = count($this->injectProvider());

        $this->assertSame($expectedCount, count($this->getSubject()->getInjectDefinitions()));
    }

    /**
     * @dataProvider injectProvider
     */
    final public function testInjectDefinition(ExpectedInject $expectedInject) : void {
        (new AssertExpectedInjectDefinition($this))->assert($expectedInject, $this->getSubject());
    }
}

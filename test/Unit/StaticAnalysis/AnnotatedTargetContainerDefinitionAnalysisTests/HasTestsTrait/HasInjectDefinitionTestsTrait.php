<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects\ExpectedInject;
use PHPUnit\Framework\Attributes\DataProvider;

trait HasInjectDefinitionTestsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    abstract public static function injectProvider() : array;

    final public function testInjectDefinitionCount() : void {
        $expectedCount = count($this->injectProvider());

        $this->assertSame($expectedCount, count($this->getSubject()->injectDefinitions()));
    }

    #[DataProvider('injectProvider')]
    final public function testInjectDefinition(ExpectedInject $expectedInject) : void {
        (new AssertExpectedInjectDefinition($this))->assert($expectedInject, $this->getSubject());
    }
}

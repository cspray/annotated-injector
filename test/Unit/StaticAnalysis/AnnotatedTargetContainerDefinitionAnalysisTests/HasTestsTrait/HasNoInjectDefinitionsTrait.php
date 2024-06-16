<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasNoInjectDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    final public function testHasNoInjectDefinitions() : void {
        $this->assertEmpty($this->getSubject()->injectDefinitions());
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasNoServiceDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    public function testHasNoServiceDefinitions() : void {
        $this->assertEmpty($this->getSubject()->serviceDefinitions());
    }
}

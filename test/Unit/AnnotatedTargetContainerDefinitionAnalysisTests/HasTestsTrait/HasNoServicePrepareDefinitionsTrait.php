<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasNoServicePrepareDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    final public function testHasNoServicePrepareDefinitions() : void {
        $this->assertEmpty($this->getSubject()->servicePrepareDefinitions());
    }
}

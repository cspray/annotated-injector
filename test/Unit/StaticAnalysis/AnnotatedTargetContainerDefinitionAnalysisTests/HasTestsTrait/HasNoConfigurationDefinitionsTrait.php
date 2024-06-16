<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasNoConfigurationDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    final public function testHasNoConfigurationDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getConfigurationDefinitions());
    }
}

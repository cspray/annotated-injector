<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\AnnotatedTargetContainerDefinitionAnalysisTests\HasTestsTrait;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

trait HasNoAliasDefinitionsTrait {

    abstract protected function getSubject() : ContainerDefinition;

    public function testHasNoAliasDefinitions() : void {
        $this->assertEmpty($this->getSubject()->getAliasDefinitions());
    }
}

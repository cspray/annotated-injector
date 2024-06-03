<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\ContainerFactory\AliasResolution;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;

interface AliasDefinitionResolution {

    public function getAliasResolutionReason() : AliasResolutionReason;

    public function getAliasDefinition() : ?AliasDefinition;
}

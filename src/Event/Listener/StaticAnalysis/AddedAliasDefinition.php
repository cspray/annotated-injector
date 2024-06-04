<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;

interface AddedAliasDefinition {

    public function handleAddedAliasDefinition(AliasDefinition $aliasDefinition) : void;
}

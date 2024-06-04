<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Event\Listener;

interface AddedAliasDefinition extends Listener {

    public function handleAddedAliasDefinition(AliasDefinition $aliasDefinition) : void;
}

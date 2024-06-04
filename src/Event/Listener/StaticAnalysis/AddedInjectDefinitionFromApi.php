<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Event\Listener;

interface AddedInjectDefinitionFromApi extends Listener {

    public function handleAddedInjectDefinitionFromApi(InjectDefinition $injectDefinition) : void;
}

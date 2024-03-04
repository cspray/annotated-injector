<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;

interface AddedInjectDefinitionFromApi {

    public function handleAddedInjectDefinitionFromApi(InjectDefinition $injectDefinition) : void;

}

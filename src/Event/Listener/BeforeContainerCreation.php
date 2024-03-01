<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface BeforeContainerCreation {

    public function handleBeforeContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition) : void;

}
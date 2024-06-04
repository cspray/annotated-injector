<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\ContainerFactory;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\Profiles;

interface BeforeContainerCreation extends Listener {

    public function handleBeforeContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition) : void;
}

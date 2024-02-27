<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Profiles;

/**
 * @deprecated
 */
interface ContainerCreatedObserver {

    public function notifyContainerCreated(Profiles $activeProfiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void;
}

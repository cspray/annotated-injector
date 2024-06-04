<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\ContainerFactory;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\Profiles;

interface AfterContainerCreation extends Listener {

    public function handleAfterContainerCreation(Profiles $profiles, ContainerDefinition $containerDefinition, AnnotatedContainer $container) : void;
}

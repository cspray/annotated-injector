<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\ContainerFactory;

use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface InjectingMethodParameter extends Listener {

    public function handleInjectingMethodParameter(Profiles $profiles, InjectDefinition $definition) : void;
}

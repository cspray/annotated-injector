<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface InjectingMethodParameter {

    public function handleInjectingMethodParameter(Profiles $profiles, InjectDefinition $definition) : void;

}

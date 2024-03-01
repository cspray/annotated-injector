<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface InjectingProperty {

    public function handleInjectingProperty(Profiles $profiles, InjectDefinition $injectDefinition) : void;

}
<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ServiceDelegated {

    public function handle(Profiles $profiles, ServiceDelegateDefinition $definition) : void;

}

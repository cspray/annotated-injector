<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ServiceFilteredDueToProfiles {

    public function handleServiceFilteredDueToProfiles(Profiles $profiles, ServiceDefinition $serviceDefinition) : void;

}
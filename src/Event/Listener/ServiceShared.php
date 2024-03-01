<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ServiceShared {

    public function handle(Profiles $profiles, ServiceDefinition $serviceDefinition) : void;

}

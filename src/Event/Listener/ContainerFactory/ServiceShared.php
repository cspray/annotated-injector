<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\ContainerFactory;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\Profiles;

interface ServiceShared extends Listener {

    public function handleServiceShared(Profiles $profiles, ServiceDefinition $serviceDefinition) : void;
}

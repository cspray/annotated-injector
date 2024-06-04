<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\ContainerFactory;

use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ServicePrepared {

    public function handleServicePrepared(Profiles $profiles, ServicePrepareDefinition $definition) : void;
}

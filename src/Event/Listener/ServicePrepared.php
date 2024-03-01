<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Profiles;

interface ServicePrepared {

    public function handle(Profiles $profiles, ServicePrepareDefinition $definition) : void;

}

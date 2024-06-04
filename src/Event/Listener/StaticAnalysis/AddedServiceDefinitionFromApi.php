<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Event\Listener;

interface AddedServiceDefinitionFromApi extends Listener {

    public function handleAddedServiceDefinitionFromApi(ServiceDefinition $serviceDefinition) : void;
}

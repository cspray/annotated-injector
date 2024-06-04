<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;

interface AddedServiceDefinitionFromApi {

    public function handleAddedServiceDefinitionFromApi(ServiceDefinition $serviceDefinition) : void;
}

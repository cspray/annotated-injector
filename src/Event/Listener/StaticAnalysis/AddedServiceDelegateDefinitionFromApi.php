<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;

interface AddedServiceDelegateDefinitionFromApi {

    public function handleAddedServiceDelegateDefinitionFromApi(ServiceDelegateDefinition $serviceDelegateDefinition) : void;
}

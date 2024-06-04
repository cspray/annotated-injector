<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Event\Listener;

interface AddedServiceDelegateDefinitionFromApi extends Listener {

    public function handleAddedServiceDelegateDefinitionFromApi(ServiceDelegateDefinition $serviceDelegateDefinition) : void;
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;

interface AddedServicePrepareDefinitionFromApi {

    public function handleAddedServicePrepareDefinitionFromApi(ServicePrepareDefinition $servicePrepareDefinition) : void;
}

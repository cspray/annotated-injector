<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\Listener;

interface AddedServicePrepareDefinitionFromApi extends Listener {

    public function handleAddedServicePrepareDefinitionFromApi(ServicePrepareDefinition $servicePrepareDefinition) : void;
}

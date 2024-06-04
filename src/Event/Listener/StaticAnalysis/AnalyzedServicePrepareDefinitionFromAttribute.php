<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface AnalyzedServicePrepareDefinitionFromAttribute extends Listener {

    public function handleAnalyzedServicePrepareDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServicePrepareDefinition $definition) : void;
}

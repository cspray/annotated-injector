<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface AnalyzedServiceDefinitionFromAttribute extends Listener {

    public function handleAnalyzedServiceDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDefinition $serviceDefinition) : void;
}

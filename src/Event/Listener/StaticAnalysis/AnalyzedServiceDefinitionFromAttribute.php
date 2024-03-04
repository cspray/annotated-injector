<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface AnalyzedServiceDefinitionFromAttribute {

    public function handleAnalyzedServiceDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDefinition $serviceDefinition) : void;

}

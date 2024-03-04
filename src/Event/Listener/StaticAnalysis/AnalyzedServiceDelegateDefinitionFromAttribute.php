<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface AnalyzedServiceDelegateDefinitionFromAttribute {

    public function handleAnalyzedServiceDelegateDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, ServiceDelegateDefinition $definition) : void;

}

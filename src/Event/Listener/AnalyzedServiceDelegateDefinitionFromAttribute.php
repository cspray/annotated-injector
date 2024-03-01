<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface AnalyzedServiceDelegateDefinitionFromAttribute {

    public function handle(AnnotatedTarget $annotatedTarget, ServiceDelegateDefinition $definition) : void;

}

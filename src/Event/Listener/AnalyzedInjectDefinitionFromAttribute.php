<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface AnalyzedInjectDefinitionFromAttribute {

    public function handleAnalyzedInjectDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, InjectDefinition $injectDefinition) : void;

}
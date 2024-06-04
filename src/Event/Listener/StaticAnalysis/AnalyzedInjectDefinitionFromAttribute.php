<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedTarget\AnnotatedTarget;

interface AnalyzedInjectDefinitionFromAttribute extends Listener {

    public function handleAnalyzedInjectDefinitionFromAttribute(AnnotatedTarget $annotatedTarget, InjectDefinition $injectDefinition) : void;
}

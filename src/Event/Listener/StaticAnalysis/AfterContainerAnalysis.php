<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\Event\Listener;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;

interface AfterContainerAnalysis extends Listener {

    public function handleAfterContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions, ContainerDefinition $containerDefinition) : void;
}

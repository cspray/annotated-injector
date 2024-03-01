<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;

interface AfterContainerAnalysis {

    public function handle(ContainerDefinitionAnalysisOptions $analysisOptions, ContainerDefinition $containerDefinition) : void;

}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener;

use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;

interface BeforeContainerAnalysis {

    public function handle(ContainerDefinitionAnalysisOptions $analysisOptions) : void;

}
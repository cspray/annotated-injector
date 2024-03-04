<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Event\Listener\StaticAnalysis;

use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;

interface BeforeContainerAnalysis {

    public function handleBeforeContainerAnalysis(ContainerDefinitionAnalysisOptions $analysisOptions) : void;

}
<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalysisOptions;
use Cspray\AnnotatedContainer\StaticAnalysis\ContainerDefinitionAnalyzer;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

final class StubContainerDefinitionAnalyzer implements ContainerDefinitionAnalyzer {

    public function __construct(
        private readonly ContainerDefinition $containerDefinition
    ) {
    }

    public function analyze(ContainerDefinitionAnalysisOptions $ContainerDefinitionAnalysisOptions) : ContainerDefinition {
        return $this->containerDefinition;
    }
}

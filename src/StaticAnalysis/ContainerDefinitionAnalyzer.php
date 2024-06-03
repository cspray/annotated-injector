<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

/**
 * An object that knows how to create a ContainerDefinition instance from a given set of options.
 */
interface ContainerDefinitionAnalyzer {

    /**
     * Generate a ContainerDefinition defined by the $containerDefinitionCompileOptions.
     *
     * Throw an InvalidCompileOptionsException if some option passed is not valid or would result in an inability
     * to properly parse a ContainerDefinition.
     *
     * Throw an InvalidAnnotationException if some source code is annotated in such a way that a compilation error
     * occurs.
     *
     * @param ContainerDefinitionAnalysisOptions $containerDefinitionCompileOptions
     * @return ContainerDefinition
     */
    public function analyze(ContainerDefinitionAnalysisOptions $containerDefinitionCompileOptions) : ContainerDefinition;
}

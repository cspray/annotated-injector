<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\ArchitecturalDecisionRecords\SingleEntrypointDefinitionProvider;

/**
 * Represents configurable details for the compilation of a ContainerDefinition.
 */
interface ContainerDefinitionAnalysisOptions {

    /**
     * Return a list of directories to scan for annotated services.
     *
     * @return list<string>
     */
    public function scanDirectories() : array;

    /**
     * If you need to modify the ContainerDefinitionBuilder return a proper consumer, otherwise null.
     *
     * This is the primary entrypoint for adding third-party services that can't be annotated to the container.
     *
     * @return DefinitionProvider|null
     */
    #[SingleEntrypointDefinitionProvider]
    public function definitionProvider() : ?DefinitionProvider;
}

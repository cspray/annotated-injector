<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

/**
 * An interface that allows for making modifications to the ContainerDefinitionBuilder before the resultant
 * ContainerDefinition is built.
 */
interface DefinitionProvider {

    /**
     * Add definitions to the $context->getBuilder(), then pass the new builder instance to $context->setBuilder().
     *
     * @param DefinitionProviderContext $context
     */
    public function consume(DefinitionProviderContext $context) : void;
}

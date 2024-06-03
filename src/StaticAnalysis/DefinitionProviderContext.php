<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;

/**
 * An object that allows the functional API for creating definition instances to work with the immutable
 * ContainerDefinitionBuilder in a "friendly" way.
 *
 * This concept allows our functional API to return the definition type it is creating without having to require the
 * end user to do a bunch of interacting with the ContainerDefinitionBuilder to properly add the definition.
 */
interface DefinitionProviderContext {

    /**
     * Return the current builder.
     *
     * @return ContainerDefinitionBuilder
     */
    public function getBuilder() : ContainerDefinitionBuilder;

    /**
     * Change the current builder; this should be called after the functional API has adjusted the existing builder and
     * a new immutable instance has been created.
     *
     * @param ContainerDefinitionBuilder $containerDefinitionBuilder
     * @return void
     */
    public function setBuilder(ContainerDefinitionBuilder $containerDefinitionBuilder) : void;
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\StaticAnalysis;

use Cspray\AnnotatedContainer\Definition\AliasDefinition;
use Cspray\AnnotatedContainer\Definition\ContainerDefinitionBuilder;
use Cspray\AnnotatedContainer\Definition\InjectDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDefinition;
use Cspray\AnnotatedContainer\Definition\ServiceDelegateDefinition;
use Cspray\AnnotatedContainer\Definition\ServicePrepareDefinition;

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

    public function addServiceDefinition(ServiceDefinition $serviceDefinition) : void;

    public function addServicePrepareDefinition(ServicePrepareDefinition $servicePrepareDefinition) : void;

    public function addServiceDelegateDefinition(ServiceDelegateDefinition $serviceDelegateDefinition) : void;

    public function addInjectDefinition(InjectDefinition $injectDefinition) : void;

    public function addAliasDefinition(AliasDefinition $aliasDefinition) : void;
}

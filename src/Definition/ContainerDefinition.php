<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Definition;

/**
 * The heart of the AnnotatedContainer; the ContainerDefinition, an object which defines how a Container should be
 * configured.
 *
 * @see ContainerDefinitionBuilder
 */
interface ContainerDefinition {

    /**
     * Return a set of ServiceDefinitions that this Container is aware of.
     *
     * @return list<ServiceDefinition>
     */
    public function getServiceDefinitions() : array;

    /**
     * Returns a set of AliasDefinition that define which concrete services are possible candidate for a given abstract
     * service.
     *
     * Note that it is possible for an abstract service to have multiple AliasDefinition defined for it. It is not the
     * job of the ContainerDefinition to determine what should be done in this situation; it is meant to simply provide
     * the results of a configuration for a Container and an annotated configuration is possible to have multiple
     * concrete services that could satisfy an abstract service. It is the responsibility of the ContainerFactory
     * or a LogicalConstraintValidator to recognize multiple aliases are possible and take the appropriate steps.
     *
     * @return list<AliasDefinition>
     */
    public function getAliasDefinitions() : array;

    /**
     * Returns a set of ServicePrepareDefinition that determine which service methods will be automatically invoked
     * after service construction.
     *
     * @return list<ServicePrepareDefinition>
     */
    public function getServicePrepareDefinitions() : array;

    /**
     * Returns a set of ServiceDelegateDefinition that determine which services require factories to be constructed.
     *
     * @return list<ServiceDelegateDefinition>
     */
    public function getServiceDelegateDefinitions() : array;

    /**
     * Returns a set of InjectDefinition that determine what values are injected into methods or properties that
     * cannot be reliably autowired.
     *
     * @return list<InjectDefinition>
     */
    public function getInjectDefinitions() : array;
}

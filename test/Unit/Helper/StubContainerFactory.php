<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\AnnotatedContainer;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactory;
use Cspray\AnnotatedContainer\ContainerFactory\ContainerFactoryOptions;
use Cspray\AnnotatedContainer\ContainerFactory\ParameterStore;
use Cspray\AnnotatedContainer\Definition\ContainerDefinition;

final class StubContainerFactory implements ContainerFactory {

    public function __construct(
        private readonly AnnotatedContainer $container
    ) {
    }

    public function createContainer(ContainerDefinition $containerDefinition, ContainerFactoryOptions $containerFactoryOptions = null) : AnnotatedContainer {
        return $this->container;
    }

    public function addParameterStore(ParameterStore $parameterStore) : void {
        // TODO: Implement addParameterStore() method.
    }
}

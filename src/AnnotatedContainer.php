<?php

namespace Cspray\AnnotatedContainer;

use Psr\Container\ContainerInterface;
use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\AnnotatedContainer\Autowire\AutowireableInvoker;

interface AnnotatedContainer extends ContainerInterface, AutowireableFactory, AutowireableInvoker {

    public function getBackingContainer() : object;
}

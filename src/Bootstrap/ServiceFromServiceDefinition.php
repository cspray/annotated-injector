<?php

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\Definition\ServiceDefinition;

/**
 * @template Service
 */
interface ServiceFromServiceDefinition {

    /**
     * @return Service
     */
    public function getService() : object;

    public function getDefinition() : ServiceDefinition;
}

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
    public function service() : object;

    public function definition() : ServiceDefinition;
}

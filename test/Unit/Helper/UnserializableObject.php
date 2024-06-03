<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

class UnserializableObject {

    private $callable;

    public function __construct() {
        $this->callable = function() {
        };
    }
}

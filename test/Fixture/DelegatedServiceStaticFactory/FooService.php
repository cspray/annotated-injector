<?php

namespace Cspray\AnnotatedContainer\Fixture\DelegatedServiceStaticFactory;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooService {

    public function getValue() : string {
        return 'From FooService';
    }
}

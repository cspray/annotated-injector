<?php

namespace Cspray\AnnotatedContainer\Fixture\DelegatedServiceStaticFactory;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface ServiceInterface {

    public function getValue() : string;
}

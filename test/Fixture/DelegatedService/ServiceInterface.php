<?php

namespace Cspray\AnnotatedContainer\Fixture\DelegatedService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface ServiceInterface {

    public function getValue() : string;
}

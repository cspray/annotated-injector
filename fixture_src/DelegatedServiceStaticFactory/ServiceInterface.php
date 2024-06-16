<?php

namespace Cspray\AnnotatedContainerFixture\DelegatedServiceStaticFactory;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
interface ServiceInterface {

    public function getValue() : string;

}
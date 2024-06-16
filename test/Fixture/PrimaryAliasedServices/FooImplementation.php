<?php

namespace Cspray\AnnotatedContainer\Fixture\PrimaryAliasedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(primary: true)]
class FooImplementation implements FooInterface {

}

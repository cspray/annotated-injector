<?php

namespace Cspray\AnnotatedContainer\Fixture\NamedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(name: 'foo')]
interface FooInterface {
}

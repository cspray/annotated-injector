<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\SubNamespacedServices\Foo;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Fixture\SubNamespacedServices\FooInterface;

#[Service]
class FooImplementation implements FooInterface {

}

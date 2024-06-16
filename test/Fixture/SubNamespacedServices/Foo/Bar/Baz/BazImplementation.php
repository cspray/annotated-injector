<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\SubNamespacedServices\Foo\Bar\Baz;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Fixture\SubNamespacedServices\BazInterface;

#[Service]
class BazImplementation implements BazInterface {

}

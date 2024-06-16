<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\SubNamespacedServices\Foo\Bar;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Fixture\SubNamespacedServices\BarInterface;

#[Service]
class BarImplementation implements BarInterface {

}

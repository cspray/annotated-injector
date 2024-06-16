<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceIntersectUnionServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooBarImplementation implements FooInterface, BarInterface {

}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceCollection;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
final class BazImplementation implements FooInterface {

}

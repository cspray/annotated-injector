<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\NamedProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['dev'], name: 'dev-foo')]
class DevFooImplementation implements FooInterface {

}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\NamedProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['test'], name: 'test-foo')]
class TestFooImplementation implements FooInterface {

}

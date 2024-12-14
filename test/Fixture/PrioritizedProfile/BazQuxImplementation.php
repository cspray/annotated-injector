<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\PrioritizedProfile;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['baz', 'qux'])]
final class BazQuxImplementation implements FooInterface {

}

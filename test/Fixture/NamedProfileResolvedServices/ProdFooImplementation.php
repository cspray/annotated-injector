<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\NamedProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['prod'], name: 'prod-foo')]
class ProdFooImplementation implements FooInterface {

}

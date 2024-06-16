<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\ProfileResolvedServices;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(profiles: ['prod'])]
class ProdFooImplementation implements FooInterface {

}

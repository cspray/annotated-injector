<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceType;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service, DummyService]
class FooService {

}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints\MultiplePrimaryService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(primary: true)]
final class BarService implements FooInterface {
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceName;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service(name: 'foo')]
class BarService {

}

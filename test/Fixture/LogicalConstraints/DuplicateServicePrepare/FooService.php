<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServicePrepare;

use Cspray\AnnotatedContainer\Attribute\Service;
use Cspray\AnnotatedContainer\Attribute\ServicePrepare;

#[Service]
class FooService {

    #[ServicePrepare, DummyPrepare]
    public function postConstruct() {
    }
}

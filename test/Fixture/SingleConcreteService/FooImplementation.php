<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\SingleConcreteService;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooImplementation {

    public function postConstruct() : void {
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\DelegatedServiceWithInjectedParameter;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooService implements ServiceInterface {

    public function __construct(
        public string $value
    ) {
    }
}

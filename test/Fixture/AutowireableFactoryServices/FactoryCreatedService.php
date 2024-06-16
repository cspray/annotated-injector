<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\AutowireableFactoryServices;

class FactoryCreatedService {

    public function __construct(
        public readonly FooInterface $foo,
        public readonly string $scalar
    ) {
    }
}

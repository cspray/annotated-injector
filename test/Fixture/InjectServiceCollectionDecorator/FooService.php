<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceCollectionDecorator;

use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooService {

    public function __construct(
        public readonly FooInterface $foo
    ) {
    }
}

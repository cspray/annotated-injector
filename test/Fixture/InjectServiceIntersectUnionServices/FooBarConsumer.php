<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceIntersectUnionServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class FooBarConsumer {

    public function __construct(
        #[Inject(FooBarImplementation::class)]
        public readonly FooInterface&BarInterface $fooBar
    ) {
    }
}

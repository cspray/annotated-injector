<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectIntersectCustomStoreServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class IntersectInjector {

    public function __construct(
        #[Inject('fooBar', from: 'intersect-store')] public readonly FooInterface&BarInterface $fooAndBar
    ) {
    }
}

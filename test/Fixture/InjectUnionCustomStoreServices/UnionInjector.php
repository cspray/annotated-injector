<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectUnionCustomStoreServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class UnionInjector {

    public function __construct(
        #[Inject('foo', from: 'union-store')] public readonly FooInterface|BarInterface $fooOrBar
    ) {
    }
}

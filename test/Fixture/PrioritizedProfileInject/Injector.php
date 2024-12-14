<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\PrioritizedProfileInject;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
final class Injector {

    public function __construct(
        #[Inject('baz-qux', profiles: ['baz', 'qux'])]
        #[Inject('default')]
        #[Inject('foo', profiles: ['foo'])]
        public readonly string $value
    ) {
    }
}

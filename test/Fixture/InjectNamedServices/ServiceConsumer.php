<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectNamedServices;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\Service;

#[Service]
class ServiceConsumer {

    public function __construct(
        #[Inject('foo')] public readonly FooInterface $foo,
        #[Inject('bar')] public readonly FooInterface $bar
    ) {
    }
}

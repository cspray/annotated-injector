<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Fixture\PrioritizedProfileInject\Injector;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class PrioritizedProfileInjectFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/PrioritizedProfileInject';
    }

    public function injector() : Type {
        return types()->class(Injector::class);
    }
}

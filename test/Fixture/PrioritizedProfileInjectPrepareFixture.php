<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Fixture\PrioritizedProfileInjectPrepare\Injector;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class PrioritizedProfileInjectPrepareFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/PrioritizedProfileInjectPrepare';
    }

    public function injector() : Type {
        return types()->class(Injector::class);
    }
}

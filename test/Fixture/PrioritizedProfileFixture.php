<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Fixture\PrioritizedProfile\BarImplementation;
use Cspray\AnnotatedContainer\Fixture\PrioritizedProfile\BazQuxImplementation;
use Cspray\AnnotatedContainer\Fixture\PrioritizedProfile\DefaultImplementation;
use Cspray\AnnotatedContainer\Fixture\PrioritizedProfile\FooImplementation;
use Cspray\AnnotatedContainer\Fixture\PrioritizedProfile\FooInterface;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class PrioritizedProfileFixture implements Fixture {
    public function getPath() : string {
        return __DIR__ . '/PrioritizedProfile';
    }

    public function fooInterface() : Type {
        return types()->class(FooInterface::class);
    }

    public function defaultImplementation() : Type {
        return types()->class(DefaultImplementation::class);
    }

    public function fooImplementation() : Type {
        return types()->class(FooImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(BarImplementation::class);
    }

    public function bazQuxImplementation() : Type {
        return types()->class(BazQuxImplementation::class);
    }
}

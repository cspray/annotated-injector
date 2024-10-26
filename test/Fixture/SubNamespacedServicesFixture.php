<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class SubNamespacedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/SubNamespacedServices';
    }

    public function barInterface() : Type {
        return types()->class(SubNamespacedServices\BarInterface::class);
    }

    public function bazInterface() : Type {
        return types()->class(SubNamespacedServices\BazInterface::class);
    }

    public function fooInterface() : Type {
        return types()->class(SubNamespacedServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(SubNamespacedServices\Foo\FooImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(SubNamespacedServices\Foo\Bar\BarImplementation::class);
    }

    public function bazImplementation() : Type {
        return types()->class(SubNamespacedServices\Foo\Bar\Baz\BazImplementation::class);
    }
}

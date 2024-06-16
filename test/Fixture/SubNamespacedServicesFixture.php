<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class SubNamespacedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/SubNamespacedServices';
    }

    public function barInterface() : ObjectType {
        return objectType(SubNamespacedServices\BarInterface::class);
    }

    public function bazInterface() : ObjectType {
        return objectType(SubNamespacedServices\BazInterface::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(SubNamespacedServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(SubNamespacedServices\Foo\FooImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(SubNamespacedServices\Foo\Bar\BarImplementation::class);
    }

    public function bazImplementation() : ObjectType {
        return objectType(SubNamespacedServices\Foo\Bar\Baz\BazImplementation::class);
    }
}

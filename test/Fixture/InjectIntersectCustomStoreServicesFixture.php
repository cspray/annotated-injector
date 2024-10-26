<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class InjectIntersectCustomStoreServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectIntersectCustomStoreServices';
    }

    public function barInterface() : Type {
        return types()->class(InjectIntersectCustomStoreServices\BarInterface::class);
    }

    public function fooInterface() : Type {
        return types()->class(InjectIntersectCustomStoreServices\FooInterface::class);
    }

    public function intersectInjector() : Type {
        return types()->class(InjectIntersectCustomStoreServices\IntersectInjector::class);
    }

    public function fooBarImplementation() : Type {
        return types()->class(InjectIntersectCustomStoreServices\FooBarImplementation::class);
    }
}

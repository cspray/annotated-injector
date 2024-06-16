<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectIntersectCustomStoreServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectIntersectCustomStoreServices';
    }

    public function barInterface() : ObjectType {
        return objectType(InjectIntersectCustomStoreServices\BarInterface::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectIntersectCustomStoreServices\FooInterface::class);
    }

    public function intersectInjector() : ObjectType {
        return objectType(InjectIntersectCustomStoreServices\IntersectInjector::class);
    }

    public function fooBarImplementation() : ObjectType {
        return objectType(InjectIntersectCustomStoreServices\FooBarImplementation::class);
    }
}

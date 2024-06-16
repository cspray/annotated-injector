<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectServiceIntersectConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceIntersectUnionServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectServiceIntersectUnionServices\FooInterface::class);
    }

    public function barInterface() : ObjectType {
        return objectType(InjectServiceIntersectUnionServices\BarInterface::class);
    }

    public function fooBarImplementation() : ObjectType {
        return objectType(InjectServiceIntersectUnionServices\FooBarImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(InjectServiceIntersectUnionServices\BarImplementation::class);
    }

    public function fooBarConsumer() : ObjectType {
        return objectType(InjectServiceIntersectUnionServices\FooBarConsumer::class);
    }
}

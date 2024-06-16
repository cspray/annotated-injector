<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectUnionCustomStoreServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectUnionCustomStoreServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectUnionCustomStoreServices\FooInterface::class);
    }

    public function barInterface() : ObjectType {
        return objectType(InjectUnionCustomStoreServices\BarInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(InjectUnionCustomStoreServices\FooImplementation::class);
    }

    public function unionInjector() : ObjectType {
        return objectType(InjectUnionCustomStoreServices\UnionInjector::class);
    }
}

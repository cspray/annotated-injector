<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectServiceCollectionDecoratorFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceCollectionDecorator';
    }

    public function fooService() : ObjectType {
        return objectType(InjectServiceCollectionDecorator\FooService::class);
    }

    public function compositeFoo() : ObjectType {
        return objectType(InjectServiceCollectionDecorator\CompositeFooImplementation::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectServiceCollectionDecorator\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(InjectServiceCollectionDecorator\FooImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(InjectServiceCollectionDecorator\BarImplementation::class);
    }

    public function bazImplementation() : ObjectType {
        return objectType(InjectServiceCollectionDecorator\BazImplementation::class);
    }
}

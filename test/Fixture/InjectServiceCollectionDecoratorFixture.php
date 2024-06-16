<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\InjectServiceCollectionDecorator\BarImplementation;
use Cspray\AnnotatedContainerFixture\InjectServiceCollectionDecorator\BazImplementation;
use Cspray\AnnotatedContainerFixture\InjectServiceCollectionDecorator\CompositeFooImplementation;
use Cspray\AnnotatedContainerFixture\InjectServiceCollectionDecorator\FooImplementation;
use Cspray\AnnotatedContainerFixture\InjectServiceCollectionDecorator\FooInterface;
use Cspray\AnnotatedContainerFixture\InjectServiceCollectionDecorator\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectServiceCollectionDecoratorFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceCollectionDecorator';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

    public function compositeFoo() : ObjectType {
        return objectType(CompositeFooImplementation::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(FooImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(BarImplementation::class);
    }

    public function bazImplementation() : ObjectType {
        return objectType(BazImplementation::class);
    }
}
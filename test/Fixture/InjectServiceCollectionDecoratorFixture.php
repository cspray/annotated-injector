<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class InjectServiceCollectionDecoratorFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceCollectionDecorator';
    }

    public function fooService() : Type {
        return types()->class(InjectServiceCollectionDecorator\FooService::class);
    }

    public function compositeFoo() : Type {
        return types()->class(InjectServiceCollectionDecorator\CompositeFooImplementation::class);
    }

    public function fooInterface() : Type {
        return types()->class(InjectServiceCollectionDecorator\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(InjectServiceCollectionDecorator\FooImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(InjectServiceCollectionDecorator\BarImplementation::class);
    }

    public function bazImplementation() : Type {
        return types()->class(InjectServiceCollectionDecorator\BazImplementation::class);
    }
}

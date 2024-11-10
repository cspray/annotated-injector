<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class InjectServiceIntersectConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceIntersectUnionServices';
    }

    public function fooInterface() : Type {
        return types()->class(InjectServiceIntersectUnionServices\FooInterface::class);
    }

    public function barInterface() : Type {
        return types()->class(InjectServiceIntersectUnionServices\BarInterface::class);
    }

    public function fooBarImplementation() : Type {
        return types()->class(InjectServiceIntersectUnionServices\FooBarImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(InjectServiceIntersectUnionServices\BarImplementation::class);
    }

    public function fooBarConsumer() : Type {
        return types()->class(InjectServiceIntersectUnionServices\FooBarConsumer::class);
    }
}

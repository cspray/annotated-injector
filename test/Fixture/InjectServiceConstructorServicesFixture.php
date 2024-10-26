<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class InjectServiceConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceConstructorServices';
    }

    public function fooInterface() : Type {
        return types()->class(InjectServiceConstructorServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(InjectServiceConstructorServices\FooImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(InjectServiceConstructorServices\BarImplementation::class);
    }

    public function serviceInjector() : Type {
        return types()->class(InjectServiceConstructorServices\ServiceInjector::class);
    }

    public function nullableServiceInjector() : Type {
        return types()->class(InjectServiceConstructorServices\NullableServiceInjector::class);
    }
}

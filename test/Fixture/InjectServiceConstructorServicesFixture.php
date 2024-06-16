<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectServiceConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceConstructorServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectServiceConstructorServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(InjectServiceConstructorServices\FooImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(InjectServiceConstructorServices\BarImplementation::class);
    }

    public function serviceInjector() : ObjectType {
        return objectType(InjectServiceConstructorServices\ServiceInjector::class);
    }

    public function nullableServiceInjector() : ObjectType {
        return objectType(InjectServiceConstructorServices\NullableServiceInjector::class);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class InjectPrepareServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectPrepareServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectPrepareServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(InjectPrepareServices\FooImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(InjectPrepareServices\BarImplementation::class);
    }

    public function prepareInjector() : ObjectType {
        return objectType(InjectPrepareServices\PrepareInjector::class);
    }

    public function serviceScalarUnionPrepareInjector() : ObjectType {
        return objectType(InjectPrepareServices\ServiceScalarUnionPrepareInjector::class);
    }
}

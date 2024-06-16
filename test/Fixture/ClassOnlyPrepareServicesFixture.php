<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ClassOnlyPrepareServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ClassOnlyPrepareServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(ClassOnlyPrepareServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(ClassOnlyPrepareServices\FooImplementation::class);
    }
}

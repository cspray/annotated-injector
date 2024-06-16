<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class NamedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/NamedServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(NamedServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(NamedServices\FooImplementation::class);
    }
}

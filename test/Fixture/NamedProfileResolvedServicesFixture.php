<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class NamedProfileResolvedServicesFixture implements Fixture {

    public function getPath(): string {
        return __DIR__ . '/NamedProfileResolvedServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(NamedProfileResolvedServices\FooInterface::class);
    }

    public function devImplementation() : ObjectType {
        return objectType(NamedProfileResolvedServices\DevFooImplementation::class);
    }

    public function prodImplementation() : ObjectType {
        return objectType(NamedProfileResolvedServices\ProdFooImplementation::class);
    }

    public function testImplementation() : ObjectType {
        return objectType(NamedProfileResolvedServices\TestFooImplementation::class);
    }
}

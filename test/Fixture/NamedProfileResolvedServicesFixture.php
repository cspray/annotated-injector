<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class NamedProfileResolvedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/NamedProfileResolvedServices';
    }

    public function fooInterface() : Type {
        return types()->class(NamedProfileResolvedServices\FooInterface::class);
    }

    public function devImplementation() : Type {
        return types()->class(NamedProfileResolvedServices\DevFooImplementation::class);
    }

    public function prodImplementation() : Type {
        return types()->class(NamedProfileResolvedServices\ProdFooImplementation::class);
    }

    public function testImplementation() : Type {
        return types()->class(NamedProfileResolvedServices\TestFooImplementation::class);
    }
}

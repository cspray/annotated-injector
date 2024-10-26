<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ProfileResolvedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ProfileResolvedServices';
    }

    public function fooInterface() : Type {
        return types()->class(ProfileResolvedServices\FooInterface::class);
    }

    public function devImplementation() : Type {
        return types()->class(ProfileResolvedServices\DevFooImplementation::class);
    }

    public function testImplementation() : Type {
        return types()->class(ProfileResolvedServices\TestFooImplementation::class);
    }

    public function prodImplementation() : Type {
        return types()->class(ProfileResolvedServices\ProdFooImplementation::class);
    }
}

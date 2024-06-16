<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ProfileResolvedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ProfileResolvedServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(ProfileResolvedServices\FooInterface::class);
    }

    public function devImplementation() : ObjectType {
        return objectType(ProfileResolvedServices\DevFooImplementation::class);
    }

    public function testImplementation() : ObjectType {
        return objectType(ProfileResolvedServices\TestFooImplementation::class);
    }

    public function prodImplementation() : ObjectType {
        return objectType(ProfileResolvedServices\ProdFooImplementation::class);
    }
}

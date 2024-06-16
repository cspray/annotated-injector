<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ThirdPartyServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(ThirdPartyServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(ThirdPartyServices\FooImplementation::class);
    }
}

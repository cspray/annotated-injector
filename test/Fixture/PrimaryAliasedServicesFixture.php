<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class PrimaryAliasedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/PrimaryAliasedServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(PrimaryAliasedServices\FooInterface::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(PrimaryAliasedServices\BarImplementation::class);
    }

    public function bazImplementation() : ObjectType {
        return objectType(PrimaryAliasedServices\BazImplementation::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(PrimaryAliasedServices\FooImplementation::class);
    }
}

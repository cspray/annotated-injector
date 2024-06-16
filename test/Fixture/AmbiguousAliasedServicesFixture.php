<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class AmbiguousAliasedServicesFixture implements Fixture {


    public function getPath() : string {
        return __DIR__ . '/AmbiguousAliasedServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(AmbiguousAliasedServices\FooInterface::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(AmbiguousAliasedServices\BarImplementation::class);
    }

    public function bazImplementation() : ObjectType {
        return objectType(AmbiguousAliasedServices\BazImplementation::class);
    }

    public function quxImplementation() : ObjectType {
        return objectType(AmbiguousAliasedServices\QuxImplementation::class);
    }
}

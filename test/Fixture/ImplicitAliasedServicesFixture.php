<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ImplicitAliasedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitAliasedServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(ImplicitAliasedServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(ImplicitAliasedServices\FooImplementation::class);
    }
}

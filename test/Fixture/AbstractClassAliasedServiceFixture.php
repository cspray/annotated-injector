<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class AbstractClassAliasedServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/AbstractClassImplicitAliasedServices';
    }

    public function fooAbstract() : ObjectType {
        return objectType(AbstractClassImplicitAliasedServices\AbstractFoo::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(AbstractClassImplicitAliasedServices\FooImplementation::class);
    }
}

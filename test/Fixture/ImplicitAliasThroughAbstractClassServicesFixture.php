<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ImplicitAliasThroughAbstractClassServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitAliasThroughAbstractClassServices';
    }

    public function fooAbstract() : ObjectType {
        return objectType(ImplicitAliasThroughAbstractClassServices\AbstractFoo::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(ImplicitAliasThroughAbstractClassServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(ImplicitAliasThroughAbstractClassServices\FooImplementation::class);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ImplicitAliasThroughAbstractClassServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitAliasThroughAbstractClassServices';
    }

    public function fooAbstract() : Type {
        return types()->class(ImplicitAliasThroughAbstractClassServices\AbstractFoo::class);
    }

    public function fooInterface() : Type {
        return types()->class(ImplicitAliasThroughAbstractClassServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(ImplicitAliasThroughAbstractClassServices\FooImplementation::class);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class AbstractClassAliasedServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/AbstractClassImplicitAliasedServices';
    }

    public function fooAbstract() : Type {
        return types()->class(AbstractClassImplicitAliasedServices\AbstractFoo::class);
    }

    public function fooImplementation() : Type {
        return types()->class(AbstractClassImplicitAliasedServices\FooImplementation::class);
    }
}

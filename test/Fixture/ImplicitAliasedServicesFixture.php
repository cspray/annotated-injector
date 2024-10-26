<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ImplicitAliasedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitAliasedServices';
    }

    public function fooInterface() : Type {
        return types()->class(ImplicitAliasedServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(ImplicitAliasedServices\FooImplementation::class);
    }
}

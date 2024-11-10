<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class PrimaryAliasedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/PrimaryAliasedServices';
    }

    public function fooInterface() : Type {
        return types()->class(PrimaryAliasedServices\FooInterface::class);
    }

    public function barImplementation() : Type {
        return types()->class(PrimaryAliasedServices\BarImplementation::class);
    }

    public function bazImplementation() : Type {
        return types()->class(PrimaryAliasedServices\BazImplementation::class);
    }

    public function fooImplementation() : Type {
        return types()->class(PrimaryAliasedServices\FooImplementation::class);
    }
}

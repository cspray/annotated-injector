<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class AmbiguousAliasedServicesFixture implements Fixture {


    public function getPath() : string {
        return __DIR__ . '/AmbiguousAliasedServices';
    }

    public function fooInterface() : Type {
        return types()->class(AmbiguousAliasedServices\FooInterface::class);
    }

    public function barImplementation() : Type {
        return types()->class(AmbiguousAliasedServices\BarImplementation::class);
    }

    public function bazImplementation() : Type {
        return types()->class(AmbiguousAliasedServices\BazImplementation::class);
    }

    public function quxImplementation() : Type {
        return types()->class(AmbiguousAliasedServices\QuxImplementation::class);
    }
}

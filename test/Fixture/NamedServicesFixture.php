<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class NamedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/NamedServices';
    }

    public function fooInterface() : Type {
        return types()->class(NamedServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(NamedServices\FooImplementation::class);
    }
}

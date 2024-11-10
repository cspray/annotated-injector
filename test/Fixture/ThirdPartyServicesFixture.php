<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ThirdPartyServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyServices';
    }

    public function fooInterface() : Type {
        return types()->class(ThirdPartyServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(ThirdPartyServices\FooImplementation::class);
    }
}

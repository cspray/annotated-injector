<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class MultiplePrepareServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/MultiplePrepareServices';
    }

    public function fooImplementation() : Type {
        return types()->class(MultiplePrepareServices\FooImplementation::class);
    }
}

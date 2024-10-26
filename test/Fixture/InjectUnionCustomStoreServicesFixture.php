<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class InjectUnionCustomStoreServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectUnionCustomStoreServices';
    }

    public function fooInterface() : Type {
        return types()->class(InjectUnionCustomStoreServices\FooInterface::class);
    }

    public function barInterface() : Type {
        return types()->class(InjectUnionCustomStoreServices\BarInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(InjectUnionCustomStoreServices\FooImplementation::class);
    }

    public function unionInjector() : Type {
        return types()->class(InjectUnionCustomStoreServices\UnionInjector::class);
    }
}

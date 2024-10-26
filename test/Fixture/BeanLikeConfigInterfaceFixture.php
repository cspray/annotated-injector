<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class BeanLikeConfigInterfaceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigInterface';
    }

    public function fooInterface() : Type {
        return types()->class(BeanLikeConfigInterface\FooInterface::class);
    }
}

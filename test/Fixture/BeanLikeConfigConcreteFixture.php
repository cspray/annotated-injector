<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class BeanLikeConfigConcreteFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigConcrete';
    }

    public function fooService() : Type {
        return types()->class(BeanLikeConfigConcrete\FooService::class);
    }
}

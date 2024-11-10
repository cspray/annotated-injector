<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class BeanLikeConfigAbstractFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigAbstract';
    }

    public function abstractFooService() : Type {
        return types()->class(BeanLikeConfigAbstract\AbstractFooService::class);
    }
}

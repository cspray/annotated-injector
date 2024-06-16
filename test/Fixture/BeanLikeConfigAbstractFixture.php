<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class BeanLikeConfigAbstractFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigAbstract';
    }

    public function abstractFooService() : ObjectType {
        return objectType(BeanLikeConfigAbstract\AbstractFooService::class);
    }
}

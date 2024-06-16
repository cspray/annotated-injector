<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class BeanLikeConfigInterfaceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigInterface';
    }

    public function fooInterface() : ObjectType {
        return objectType(BeanLikeConfigInterface\FooInterface::class);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class BeanLikeConfigConcreteFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigConcrete';
    }

    public function fooService() : ObjectType {
        return objectType(BeanLikeConfigConcrete\FooService::class);
    }
}

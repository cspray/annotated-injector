<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\BeanLikeConfigConcrete\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class BeanLikeConfigConcreteFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigConcrete';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

}
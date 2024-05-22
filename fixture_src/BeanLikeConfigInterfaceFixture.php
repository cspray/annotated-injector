<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\BeanLikeConfigInterface\FooInterface;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class BeanLikeConfigInterfaceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/BeanLikeConfigInterface';
    }

    public function fooInterface() : ObjectType {
        return objectType(FooInterface::class);
    }
}
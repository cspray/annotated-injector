<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\InjectServiceCollection\CollectionInjector;
use Cspray\AnnotatedContainerFixture\InjectServiceCollection\FooInterface;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectServiceCollectionFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceCollection';
    }

    public function collectionInjector() : ObjectType {
        return objectType(CollectionInjector::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(FooInterface::class);
    }

}

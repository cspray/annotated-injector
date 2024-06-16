<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectServiceCollectionFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceCollection';
    }

    public function collectionInjector() : ObjectType {
        return objectType(InjectServiceCollection\CollectionInjector::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectServiceCollection\FooInterface::class);
    }
}

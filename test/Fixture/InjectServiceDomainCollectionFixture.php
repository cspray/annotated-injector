<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectServiceDomainCollectionFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceDomainCollection';
    }

    public function collectionInjector() : ObjectType {
        return objectType(InjectServiceDomainCollection\CollectionInjector::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectServiceDomainCollection\FooInterface::class);
    }
}

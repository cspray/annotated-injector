<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;


use Cspray\AnnotatedContainerFixture\InjectServiceDomainCollection\CollectionInjector;
use Cspray\AnnotatedContainerFixture\InjectServiceDomainCollection\FooInterface;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectServiceDomainCollectionFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceDomainCollection';
    }

    public function collectionInjector() : ObjectType {
        return objectType(CollectionInjector::class);
    }

    public function fooInterface() : ObjectType {
        return objectType(FooInterface::class);
    }

}

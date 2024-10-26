<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class InjectServiceDomainCollectionFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectServiceDomainCollection';
    }

    public function collectionInjector() : Type {
        return types()->class(InjectServiceDomainCollection\CollectionInjector::class);
    }

    public function fooInterface() : Type {
        return types()->class(InjectServiceDomainCollection\FooInterface::class);
    }
}

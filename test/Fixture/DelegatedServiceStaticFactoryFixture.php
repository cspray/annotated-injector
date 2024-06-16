<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DelegatedServiceStaticFactoryFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DelegatedServiceStaticFactory';
    }

    public function serviceInterface() : ObjectType {
        return objectType(DelegatedServiceStaticFactory\ServiceInterface::class);
    }

    public function serviceFactory() : ObjectType {
        return objectType(DelegatedServiceStaticFactory\ServiceFactory::class);
    }

    public function fooService() : ObjectType {
        return objectType(DelegatedServiceStaticFactory\FooService::class);
    }
}

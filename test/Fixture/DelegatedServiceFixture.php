<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DelegatedServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DelegatedService';
    }

    public function serviceInterface() : ObjectType {
        return objectType(DelegatedService\ServiceInterface::class);
    }

    public function serviceFactory() : ObjectType {
        return objectType(DelegatedService\ServiceFactory::class);
    }

    public function fooService() : ObjectType {
        return objectType(DelegatedService\FooService::class);
    }
}

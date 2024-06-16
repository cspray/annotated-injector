<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ImplicitServiceDelegateTypeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitServiceDelegateType';
    }

    public function fooService() : ObjectType {
        return objectType(ImplicitServiceDelegateType\FooService::class);
    }

    public function fooServiceFactory() : ObjectType {
        return objectType(ImplicitServiceDelegateType\FooServiceFactory::class);
    }
}

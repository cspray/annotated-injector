<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ImplicitServiceDelegateUnionTypeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitServiceDelegateUnionType';
    }

    public function fooService() : ObjectType {
        return objectType(Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateUnionType\FooService::class);
    }

    public function barService() : ObjectType {
        return objectType(Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateUnionType\BarService::class);
    }

    public function serviceFactory() : ObjectType {
        return objectType(Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateUnionType\ServiceFactory::class);
    }
}

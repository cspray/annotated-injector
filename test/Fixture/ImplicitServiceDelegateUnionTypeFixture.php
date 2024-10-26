<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ImplicitServiceDelegateUnionTypeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitServiceDelegateUnionType';
    }

    public function fooService() : Type {
        return types()->class(Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateUnionType\FooService::class);
    }

    public function barService() : Type {
        return types()->class(Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateUnionType\BarService::class);
    }

    public function serviceFactory() : Type {
        return types()->class(Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateUnionType\ServiceFactory::class);
    }
}

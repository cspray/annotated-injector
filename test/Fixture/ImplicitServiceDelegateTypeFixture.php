<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ImplicitServiceDelegateTypeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ImplicitServiceDelegateType';
    }

    public function fooService() : Type {
        return types()->class(ImplicitServiceDelegateType\FooService::class);
    }

    public function fooServiceFactory() : Type {
        return types()->class(ImplicitServiceDelegateType\FooServiceFactory::class);
    }
}

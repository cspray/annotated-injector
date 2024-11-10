<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DelegatedServiceStaticFactoryFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DelegatedServiceStaticFactory';
    }

    public function serviceInterface() : Type {
        return types()->class(DelegatedServiceStaticFactory\ServiceInterface::class);
    }

    public function serviceFactory() : Type {
        return types()->class(DelegatedServiceStaticFactory\ServiceFactory::class);
    }

    public function fooService() : Type {
        return types()->class(DelegatedServiceStaticFactory\FooService::class);
    }
}

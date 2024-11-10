<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DelegatedServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DelegatedService';
    }

    public function serviceInterface() : Type {
        return types()->class(DelegatedService\ServiceInterface::class);
    }

    public function serviceFactory() : Type {
        return types()->class(DelegatedService\ServiceFactory::class);
    }

    public function fooService() : Type {
        return types()->class(DelegatedService\FooService::class);
    }
}

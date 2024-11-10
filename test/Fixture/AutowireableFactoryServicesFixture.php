<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class AutowireableFactoryServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/AutowireableFactoryServices';
    }

    public function fooInterface() : Type {
        return types()->class(AutowireableFactoryServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(AutowireableFactoryServices\FooImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(AutowireableFactoryServices\BarImplementation::class);
    }

    public function factoryCreatedService() : Type {
        return types()->class(AutowireableFactoryServices\FactoryCreatedService::class);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class AutowireableFactoryServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/AutowireableFactoryServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(AutowireableFactoryServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(AutowireableFactoryServices\FooImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(AutowireableFactoryServices\BarImplementation::class);
    }

    public function factoryCreatedService() : ObjectType {
        return objectType(AutowireableFactoryServices\FactoryCreatedService::class);
    }
}

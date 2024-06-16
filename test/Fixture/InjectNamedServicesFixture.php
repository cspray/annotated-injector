<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectNamedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectNamedServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(InjectNamedServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(InjectNamedServices\FooImplementation::class);
    }

    public function barImplementation() : ObjectType {
        return objectType(InjectNamedServices\BarImplementation::class);
    }

    public function serviceConsumer() : ObjectType {
        return objectType(InjectNamedServices\ServiceConsumer::class);
    }
}

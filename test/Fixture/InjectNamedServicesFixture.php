<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class InjectNamedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectNamedServices';
    }

    public function fooInterface() : Type {
        return types()->class(InjectNamedServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(InjectNamedServices\FooImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(InjectNamedServices\BarImplementation::class);
    }

    public function serviceConsumer() : Type {
        return types()->class(InjectNamedServices\ServiceConsumer::class);
    }
}

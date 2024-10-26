<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class InjectPrepareServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectPrepareServices';
    }

    public function fooInterface() : Type {
        return types()->class(InjectPrepareServices\FooInterface::class);
    }

    public function fooImplementation() : Type {
        return types()->class(InjectPrepareServices\FooImplementation::class);
    }

    public function barImplementation() : Type {
        return types()->class(InjectPrepareServices\BarImplementation::class);
    }

    public function prepareInjector() : Type {
        return types()->class(InjectPrepareServices\PrepareInjector::class);
    }

    public function serviceScalarUnionPrepareInjector() : Type {
        return types()->class(InjectPrepareServices\ServiceScalarUnionPrepareInjector::class);
    }
}

<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class InjectEnumConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectEnumConstructorServices';
    }

    public function enumInjector() : Type {
        return types()->class(InjectEnumConstructorServices\EnumInjector::class);
    }
}

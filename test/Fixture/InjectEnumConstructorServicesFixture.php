<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class InjectEnumConstructorServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/InjectEnumConstructorServices';
    }

    public function enumInjector() : ObjectType {
        return objectType(InjectEnumConstructorServices\EnumInjector::class);
    }
}

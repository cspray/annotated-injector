<?php

namespace Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateUnionType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    #[ServiceDelegate]
    public static function create() : BarService|FooService {
        return new FooService();
    }
}

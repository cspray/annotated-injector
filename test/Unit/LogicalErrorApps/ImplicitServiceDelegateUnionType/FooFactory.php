<?php

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateUnionType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() : FooService|BarService {
    }
}

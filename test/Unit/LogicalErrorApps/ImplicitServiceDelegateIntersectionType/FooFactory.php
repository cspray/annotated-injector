<?php

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateIntersectionType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() : FooService&BarService {
    }
}

<?php

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateNoType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() {
    }
}

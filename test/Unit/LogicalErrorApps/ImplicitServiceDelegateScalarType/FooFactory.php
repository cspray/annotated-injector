<?php

namespace Cspray\AnnotatedContainer\Unit\LogicalErrorApps\ImplicitServiceDelegateScalarType;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooFactory {

    #[ServiceDelegate]
    public static function create() : string {
        return '';
    }
}

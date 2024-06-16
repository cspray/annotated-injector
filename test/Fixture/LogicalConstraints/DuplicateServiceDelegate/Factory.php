<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceDelegate;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class Factory {

    #[ServiceDelegate]
    public static function createFoo() : FooService {
    }

    #[ServiceDelegate]
    public static function createFooAgain() : FooService {
    }
}

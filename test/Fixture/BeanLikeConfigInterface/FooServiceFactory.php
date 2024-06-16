<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\BeanLikeConfigInterface;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooServiceFactory {

    #[ServiceDelegate]
    public static function createFooService() : FooInterface {
        return new FooService();
    }
}

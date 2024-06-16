<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\BeanLikeConfigAbstract;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class FooServiceFactory {

    #[ServiceDelegate]
    public static function createFooService() : AbstractFooService {
        return new FooService();
    }
}

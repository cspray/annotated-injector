<?php

namespace Cspray\AnnotatedContainerFixture\BeanLikeConfigConcrete;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    #[ServiceDelegate]
    public function create() : FooService {
        return new FooService();
    }

}
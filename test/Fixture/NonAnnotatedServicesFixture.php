<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class NonAnnotatedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/NonAnnotatedServices';
    }

    public function annotatedService() : Type {
        return types()->class(NonAnnotatedServices\AnnotatedService::class);
    }

    public function nonAnnotatedService() : Type {
        return types()->class(NonAnnotatedServices\NotAnnotatedObject::class);
    }
}

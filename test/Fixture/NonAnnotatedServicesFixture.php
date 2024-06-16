<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class NonAnnotatedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/NonAnnotatedServices';
    }

    public function annotatedService() : ObjectType {
        return objectType(NonAnnotatedServices\AnnotatedService::class);
    }

    public function nonAnnotatedService() : ObjectType {
        return objectType(NonAnnotatedServices\NotAnnotatedObject::class);
    }
}

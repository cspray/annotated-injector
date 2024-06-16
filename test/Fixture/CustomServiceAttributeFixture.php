<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Fixture\CustomServiceAttribute\OtherService;
use Cspray\AnnotatedContainer\Fixture\CustomServiceAttribute\Repo;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class CustomServiceAttributeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/CustomServiceAttribute';
    }

    public function repo() : ObjectType {
        return objectType(Repo::class);
    }

    public function myRepo() : ObjectType {
        return objectType(CustomServiceAttribute\MyRepo::class);
    }

    public function otherService() : ObjectType {
        return objectType(OtherService::class);
    }
}

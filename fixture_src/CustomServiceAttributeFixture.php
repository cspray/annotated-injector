<?php

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\CustomServiceAttribute\OtherService;
use Cspray\AnnotatedContainerFixture\CustomServiceAttribute\Repo;
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
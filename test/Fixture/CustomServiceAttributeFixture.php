<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Fixture\CustomServiceAttribute\OtherService;
use Cspray\AnnotatedContainer\Fixture\CustomServiceAttribute\Repo;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class CustomServiceAttributeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/CustomServiceAttribute';
    }

    public function repo() : Type {
        return types()->class(Repo::class);
    }

    public function myRepo() : Type {
        return types()->class(CustomServiceAttribute\MyRepo::class);
    }

    public function otherService() : Type {
        return types()->class(OtherService::class);
    }
}

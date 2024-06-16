<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateNamedServiceDifferentProfilesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateNamedServiceDifferentProfiles';
    }

    public function barService() : ObjectType {
        return objectType(DuplicateNamedServiceDifferentProfiles\BarService::class);
    }

    public function fooService() : ObjectType {
        return objectType(DuplicateNamedServiceDifferentProfiles\FooService::class);
    }
}

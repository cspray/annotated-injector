<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DuplicateNamedServiceDifferentProfilesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateNamedServiceDifferentProfiles';
    }

    public function barService() : Type {
        return types()->class(DuplicateNamedServiceDifferentProfiles\BarService::class);
    }

    public function fooService() : Type {
        return types()->class(DuplicateNamedServiceDifferentProfiles\FooService::class);
    }
}

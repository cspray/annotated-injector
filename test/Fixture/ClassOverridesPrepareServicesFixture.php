<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ClassOverridesPrepareServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ClassOverridesPrepareServices';
    }

    public function fooInterface() : ObjectType {
        return objectType(ClassOverridesPrepareServices\FooInterface::class);
    }

    public function fooImplementation() : ObjectType {
        return objectType(ClassOverridesPrepareServices\FooImplementation::class);
    }
}

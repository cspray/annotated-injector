<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\ProtectedServiceDelegateMethod\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class ProtectedServiceDelegateFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ProtectedServiceDelegateMethod';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceDelegate\Factory;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceDelegate\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

class DuplicateServiceDelegateFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceDelegate';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

    public function factory() : ObjectType {
        return objectType(Factory::class);
    }
}

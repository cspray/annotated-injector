<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceName\BarService;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceName\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateServiceNameFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceName';
    }

    public function getBarService() : ObjectType {
        return objectType(BarService::class);
    }

    public function getFooService() : ObjectType {
        return objectType(FooService::class);
    }
}

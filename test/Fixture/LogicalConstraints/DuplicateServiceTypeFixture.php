<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceType\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateServiceTypeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceType';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }
}

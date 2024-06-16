<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServicePrepare\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class DuplicateServicePrepareFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServicePrepare';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }
}

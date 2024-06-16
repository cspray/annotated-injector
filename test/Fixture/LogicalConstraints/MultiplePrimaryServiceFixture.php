<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\MultiplePrimaryService\BarService;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\MultiplePrimaryService\FooInterface;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\MultiplePrimaryService\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class MultiplePrimaryServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/MultiplePrimaryService';
    }

    public function fooInterface() : ObjectType {
        return objectType(FooInterface::class);
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

    public function barService() : ObjectType {
        return objectType(BarService::class);
    }
}

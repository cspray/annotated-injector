<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceName\BarService;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceName\FooService;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DuplicateServiceNameFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceName';
    }

    public function getBarService() : Type {
        return types()->class(BarService::class);
    }

    public function getFooService() : Type {
        return types()->class(FooService::class);
    }
}

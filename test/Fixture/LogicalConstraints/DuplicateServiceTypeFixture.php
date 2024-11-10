<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceType\FooService;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DuplicateServiceTypeFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceType';
    }

    public function fooService() : Type {
        return types()->class(FooService::class);
    }
}

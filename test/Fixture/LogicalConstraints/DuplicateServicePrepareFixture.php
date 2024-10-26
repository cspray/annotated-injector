<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServicePrepare\FooService;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class DuplicateServicePrepareFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServicePrepare';
    }

    public function fooService() : Type {
        return types()->class(FooService::class);
    }
}

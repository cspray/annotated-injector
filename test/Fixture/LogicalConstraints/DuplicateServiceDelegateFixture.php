<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceDelegate\Factory;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\DuplicateServiceDelegate\FooService;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class DuplicateServiceDelegateFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/DuplicateServiceDelegate';
    }

    public function fooService() : Type {
        return types()->class(FooService::class);
    }

    public function factory() : Type {
        return types()->class(Factory::class);
    }
}

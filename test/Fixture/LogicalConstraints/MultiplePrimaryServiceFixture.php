<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\MultiplePrimaryService\BarService;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\MultiplePrimaryService\FooInterface;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\MultiplePrimaryService\FooService;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class MultiplePrimaryServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/MultiplePrimaryService';
    }

    public function fooInterface() : Type {
        return types()->class(FooInterface::class);
    }

    public function fooService() : Type {
        return types()->class(FooService::class);
    }

    public function barService() : Type {
        return types()->class(BarService::class);
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class SingleConcreteServiceFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/SingleConcreteService';
    }

    public function fooImplementation() : ObjectType {
        return objectType(SingleConcreteService\FooImplementation::class);
    }
}

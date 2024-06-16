<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\LogicalConstraints;

use Cspray\AnnotatedContainer\Fixture\Fixture;
use Cspray\AnnotatedContainer\Fixture\ImplicitServiceDelegateType\FooServiceFactory;
use Cspray\AnnotatedContainer\Fixture\LogicalConstraints\ProtectedServiceDelegateMethod\FooService;
use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class PrivateServiceDelegateFixture implements Fixture {


    public function getPath() : string {
        return __DIR__ . '/PrivateServiceDelegateMethod';
    }

    public function fooService() : ObjectType {
        return objectType(FooService::class);
    }

    public function privateFooServiceFactory() : ObjectType {
        return objectType(FooServiceFactory::class);
    }
}

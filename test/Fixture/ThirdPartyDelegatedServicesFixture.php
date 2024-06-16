<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\Typiphy\ObjectType;
use function Cspray\Typiphy\objectType;

final class ThirdPartyDelegatedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyDelegatedServices';
    }

    public function loggerFactory() : ObjectType {
        return objectType(ThirdPartyDelegatedServices\LoggerFactory::class);
    }
}

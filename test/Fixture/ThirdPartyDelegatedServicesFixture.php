<?php

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ThirdPartyDelegatedServicesFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyDelegatedServices';
    }

    public function loggerFactory() : Type {
        return types()->class(ThirdPartyDelegatedServices\LoggerFactory::class);
    }
}

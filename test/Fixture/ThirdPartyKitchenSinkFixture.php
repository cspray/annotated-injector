<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

class ThirdPartyKitchenSinkFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyKitchenSink';
    }
}

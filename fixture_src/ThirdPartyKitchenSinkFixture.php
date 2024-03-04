<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture;

use Cspray\AnnotatedContainerFixture\ThirdPartyKitchenSink\NonAnnotatedService;
use Cspray\Typiphy\ObjectType;

class ThirdPartyKitchenSinkFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyKitchenSink';
    }

    public function nonAnnotatedService() : ObjectType {
        return objectType(NonAnnotatedService::class);
    }

}

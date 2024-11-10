<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Fixture\ThirdPartyKitchenSink\NonAnnotatedService;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

class ThirdPartyKitchenSinkFixture implements Fixture {

    public function getPath() : string {
        return __DIR__ . '/ThirdPartyKitchenSink';
    }

    public function nonAnnotatedService() : Type {
        return types()->class(NonAnnotatedService::class);
    }
}

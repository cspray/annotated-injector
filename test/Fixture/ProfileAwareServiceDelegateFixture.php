<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture;

use Cspray\AnnotatedContainer\Fixture\ProfileAwareServiceDelegate\Factory;
use Cspray\AnnotatedContainer\Fixture\ProfileAwareServiceDelegate\ProdService;
use Cspray\AnnotatedContainer\Fixture\ProfileAwareServiceDelegate\Service;
use Cspray\AnnotatedContainer\Fixture\ProfileAwareServiceDelegate\TestService;
use Cspray\AnnotatedContainer\Reflection\Type;
use function Cspray\AnnotatedContainer\Reflection\types;

final class ProfileAwareServiceDelegateFixture implements Fixture {

    public function getPath() : string {
        return  __DIR__ . '/ProfileAwareServiceDelegate';
    }

    public function factory() : Type {
        return types()->class(Factory::class);
    }

    public function service() : Type {
        return types()->class(Service::class);
    }

    public function testService() : Type {
        return types()->class(TestService::class);
    }

    public function prodService() : Type {
        return types()->class(ProdService::class);
    }
}

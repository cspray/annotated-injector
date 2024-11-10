<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\ProfileAwareServiceDelegate;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

final class Factory {

    #[ServiceDelegate(profiles: ['test'])]
    public static function testService() : Service {
         return new TestService();
    }

    #[ServiceDelegate(profiles: ['prod'])]
    public static function prodService() : Service {
        return new ProdService();
    }
}

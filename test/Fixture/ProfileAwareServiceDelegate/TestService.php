<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\ProfileAwareServiceDelegate;

#[\Cspray\AnnotatedContainer\Attribute\Service]
final class TestService implements Service {

    public function get() : string {
        return self::class;
    }
}

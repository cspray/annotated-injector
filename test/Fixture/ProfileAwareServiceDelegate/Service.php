<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\ProfileAwareServiceDelegate;

#[\Cspray\AnnotatedContainer\Attribute\Service]
interface Service {

    public function get() : string;
}

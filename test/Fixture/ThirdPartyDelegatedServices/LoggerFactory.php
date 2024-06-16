<?php

namespace Cspray\AnnotatedContainer\Fixture\ThirdPartyDelegatedServices;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggerFactory {

    #[ServiceDelegate]
    public function create() : LoggerInterface {
        return new NullLogger();
    }
}

<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\DelegatedServiceWithInjectedParameter;

use Cspray\AnnotatedContainer\Attribute\Inject;
use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    #[ServiceDelegate]
    public function createService(
        #[Inject('my injected value')]
        string $value
    ) : ServiceInterface {
        return new FooService($value);
    }
}

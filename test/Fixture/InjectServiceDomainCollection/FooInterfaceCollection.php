<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Fixture\InjectServiceDomainCollection;

final class FooInterfaceCollection {

    public array $services;

    public function __construct(
        FooInterface...$services
    ) {
        $this->services = $services;
    }
}

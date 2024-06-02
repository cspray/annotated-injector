<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainerFixture\InjectServiceDomainCollection;

final class FooInterfaceCollection {

    public array $services;

    public function __construct(
        FooInterface... $services
    ) {
        $this->services = $services;
    }

}
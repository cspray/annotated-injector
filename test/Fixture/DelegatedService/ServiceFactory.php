<?php

namespace Cspray\AnnotatedContainer\Fixture\DelegatedService;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    public function __construct(private readonly FooService $fooService) {
    }

    #[ServiceDelegate(ServiceInterface::class)]
    public function createService() : ServiceInterface {
        return new class($this->fooService) implements ServiceInterface {

            public function __construct(private readonly FooService $fooService) {
            }

            public function getValue(): string {
                return 'From ServiceFactory ' . $this->fooService->getValue();
            }
        };
    }
}

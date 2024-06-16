<?php

namespace Cspray\AnnotatedContainerFixture\DelegatedServiceStaticFactory;

use Cspray\AnnotatedContainer\Attribute\ServiceDelegate;

class ServiceFactory {

    #[ServiceDelegate]
    public static function createService(FooService $fooService) : ServiceInterface {
        return new class($fooService) implements ServiceInterface {

            public function __construct(private readonly FooService $fooService)
            {
            }

            public function getValue(): string {
                return 'From static ServiceFactory ' . $this->fooService->getValue();
            }
        };
    }

}
<?php

namespace Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers;

use Cspray\AnnotatedContainer\Fixture\VendorScanningInitializers\Vendor\Package\SomeService;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use function Cspray\AnnotatedContainer\service;
use function Cspray\Typiphy\objectType;

class DependencyDefinitionProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        $context->addServiceDefinition(
            service(objectType(SomeService::class))
        );
    }
}

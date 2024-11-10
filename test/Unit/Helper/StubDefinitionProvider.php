<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainer\Fixture\Fixtures;
use function Cspray\AnnotatedContainer\Definition\service;

final class StubDefinitionProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        $context->addServiceDefinition(
            service(Fixtures::thirdPartyServices()->fooImplementation())
        );
    }
}

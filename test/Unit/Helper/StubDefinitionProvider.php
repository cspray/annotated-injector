<?php

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\AnnotatedContainerFixture\Fixtures;
use function Cspray\AnnotatedContainer\service;

final class StubDefinitionProvider implements DefinitionProvider {

    public function consume(DefinitionProviderContext $context) : void {
        service($context, Fixtures::thirdPartyServices()->fooImplementation());
    }
}

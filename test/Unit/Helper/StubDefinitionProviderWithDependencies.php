<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\Reflection\Type;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use function Cspray\AnnotatedContainer\Definition\service;

final class StubDefinitionProviderWithDependencies implements DefinitionProvider {

    public function __construct(private readonly Type $service) {
    }

    public function consume(DefinitionProviderContext $context) : void {
        $context->addServiceDefinition(service($this->service));
    }
}

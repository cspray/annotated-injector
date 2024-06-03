<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\Helper;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;
use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProviderContext;
use Cspray\Typiphy\ObjectType;
use function Cspray\AnnotatedContainer\service;

final class StubDefinitionProviderWithDependencies implements DefinitionProvider {

    public function __construct(private readonly ObjectType $service) {
    }

    public function consume(DefinitionProviderContext $context) : void {
        service($context, $this->service);
    }
}

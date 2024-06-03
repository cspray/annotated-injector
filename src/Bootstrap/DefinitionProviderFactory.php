<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Bootstrap;

use Cspray\AnnotatedContainer\StaticAnalysis\DefinitionProvider;

interface DefinitionProviderFactory {

    public function createProvider(string $identifier) : DefinitionProvider;
}

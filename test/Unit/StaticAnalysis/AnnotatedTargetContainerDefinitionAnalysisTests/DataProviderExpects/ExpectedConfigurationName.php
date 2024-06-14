<?php declare(strict_types=1);

namespace Cspray\AnnotatedContainer\Unit\StaticAnalysis\AnnotatedTargetContainerDefinitionAnalysisTests\DataProviderExpects;

use Cspray\Typiphy\ObjectType;

final class ExpectedConfigurationName {

    public function __construct(
        public readonly ObjectType $configuration,
        public readonly ?string $name
    ) {
    }
}
